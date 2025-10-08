<?php
// JSON Database Storage Class
class JsonDatabase {
    private static $instance = null;
    private $dataPath;
    private $cache = [];

    private function __construct() {
        $this->dataPath = __DIR__ . '/../../data/';

        // Verificar que el directorio de datos existe
        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath, 0755, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        // Para compatibilidad con el código existente
        return null;
    }

    public function isFallbackMode() {
        return false; // JSON es nuestro modo principal ahora
    }

    private function getFilePath($table) {
        return $this->dataPath . $table . '.json';
    }

    private function loadTable($table) {
        if (isset($this->cache[$table])) {
            return $this->cache[$table];
        }

        $filePath = $this->getFilePath($table);

        if (!file_exists($filePath)) {
            $this->cache[$table] = [];
            return [];
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if ($data === null) {
            error_log("Error loading JSON from $filePath: " . json_last_error_msg());
            $this->cache[$table] = [];
            return [];
        }

        $this->cache[$table] = $data;
        return $data;
    }

    private function saveTable($table, $data) {
        $filePath = $this->getFilePath($table);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            error_log("Error encoding JSON for table $table: " . json_last_error_msg());
            return false;
        }

        $result = file_put_contents($filePath, $json, LOCK_EX);

        if ($result !== false) {
            $this->cache[$table] = $data;
            return true;
        }

        error_log("Error writing JSON file $filePath");
        return false;
    }

    public function select($table, $columns = '*', $where = '', $whereParams = [], $orderBy = '', $limit = '') {
        $data = $this->loadTable($table);

        if (empty($data)) {
            return [];
        }

        // Aplicar filtros WHERE
        if ($where) {
            $data = $this->applyWhere($data, $where, $whereParams);
        }

        // Aplicar ORDER BY (básico)
        if ($orderBy) {
            $data = $this->applyOrderBy($data, $orderBy);
        }

        // Aplicar LIMIT
        if ($limit && is_numeric($limit)) {
            $data = array_slice($data, 0, (int)$limit);
        }

        return $data;
    }

    public function selectOne($table, $columns = '*', $where = '', $whereParams = []) {
        $results = $this->select($table, $columns, $where, $whereParams, '', '1');
        return !empty($results) ? $results[0] : null;
    }

    public function insert($table, $data) {
        $tableData = $this->loadTable($table);

        // Generar ID automático
        $maxId = 0;
        foreach ($tableData as $row) {
            if (isset($row['id']) && $row['id'] > $maxId) {
                $maxId = $row['id'];
            }
        }

        $data['id'] = $maxId + 1;

        // Agregar timestamps si no existen
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $tableData[] = $data;

        if ($this->saveTable($table, $tableData)) {
            return $data['id'];
        }

        return false;
    }

    public function update($table, $data, $where, $whereParams = []) {
        $tableData = $this->loadTable($table);
        $updated = 0;

        for ($i = 0; $i < count($tableData); $i++) {
            if ($this->matchesWhere($tableData[$i], $where, $whereParams)) {
                foreach ($data as $key => $value) {
                    $tableData[$i][$key] = $value;
                }
                $tableData[$i]['updated_at'] = date('Y-m-d H:i:s');
                $updated++;
            }
        }

        if ($updated > 0) {
            $this->saveTable($table, $tableData);
        }

        return $updated;
    }

    public function delete($table, $where, $whereParams = []) {
        $tableData = $this->loadTable($table);
        $deleted = 0;
        $newData = [];

        foreach ($tableData as $row) {
            if (!$this->matchesWhere($row, $where, $whereParams)) {
                $newData[] = $row;
            } else {
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->saveTable($table, $newData);
        }

        return $deleted;
    }

    private function applyWhere($data, $where, $whereParams) {
        $filtered = [];

        foreach ($data as $row) {
            if ($this->matchesWhere($row, $where, $whereParams)) {
                $filtered[] = $row;
            }
        }

        return $filtered;
    }

    private function matchesWhere($row, $where, $whereParams) {
        // Implementación básica de WHERE para casos comunes

        // Si no hay WHERE, retornar true
        if (empty($where)) {
            return true;
        }

        // Normalizar whereParams: convertir array indexado a array asociativo
        $params = [];
        if (!empty($whereParams)) {
            // Si es array indexado [valor1, valor2], extraer valores
            if (isset($whereParams[0])) {
                $params = $whereParams;
            } else {
                // Si ya tiene claves nombradas, usar tal cual
                $params = $whereParams;
            }
        }

        // Reemplazar ? con valores en orden
        $whereCopy = $where;
        $paramIndex = 0;
        while (strpos($whereCopy, '?') !== false && isset($params[$paramIndex])) {
            $value = $params[$paramIndex];
            // Si es booleano, convertir a 1/0
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            $whereCopy = preg_replace('/\?/', "'" . $value . "'", $whereCopy, 1);
            $paramIndex++;
        }

        // Manejar condiciones con AND
        if (stripos($whereCopy, ' AND ') !== false) {
            $conditions = preg_split('/\s+AND\s+/i', $whereCopy);
            foreach ($conditions as $condition) {
                if (!$this->evaluateCondition($row, trim($condition))) {
                    return false;
                }
            }
            return true;
        }

        // Evaluar condiciones simples
        // Para username = 'valor'
        if (preg_match("/username\s*=\s*'([^']+)'/", $whereCopy, $matches)) {
            return isset($row['username']) && $row['username'] === $matches[1];
        }

        // Para email = 'valor'
        if (preg_match("/email\s*=\s*'([^']+)'/", $whereCopy, $matches)) {
            return isset($row['email']) && $row['email'] === $matches[1];
        }

        // Para id = 'valor' o id = valor
        if (preg_match("/\bid\s*=\s*'?(\d+)'?/", $whereCopy, $matches)) {
            return isset($row['id']) && $row['id'] == $matches[1];
        }

        // Para user_id = 'valor' o user_id = valor
        if (preg_match("/user_id\s*=\s*'?(\d+)'?/", $whereCopy, $matches)) {
            return isset($row['user_id']) && $row['user_id'] == $matches[1];
        }

        // Para categoria_id = 'valor'
        if (preg_match("/categoria_id\s*=\s*'?(\d+)'?/", $whereCopy, $matches)) {
            return isset($row['categoria_id']) && $row['categoria_id'] == $matches[1];
        }

        // Para cliente_id = 'valor'
        if (preg_match("/cliente_id\s*=\s*'?(\d+)'?/", $whereCopy, $matches)) {
            return isset($row['cliente_id']) && $row['cliente_id'] == $matches[1];
        }

        // Para consulta_id = 'valor'
        if (preg_match("/consulta_id\s*=\s*'?(\d+)'?/", $whereCopy, $matches)) {
            return isset($row['consulta_id']) && $row['consulta_id'] == $matches[1];
        }

        // Para ruta_archivo = 'valor'
        if (preg_match("/ruta_archivo\s*=\s*'([^']+)'/", $whereCopy, $matches)) {
            return isset($row['ruta_archivo']) && $row['ruta_archivo'] == $matches[1];
        }

        // Para rut_empresa = 'valor'
        if (preg_match("/rut_empresa\s*=\s*'([^']+)'/", $whereCopy, $matches)) {
            return isset($row['rut_empresa']) && $row['rut_empresa'] == $matches[1];
        }

        // Para is_active = TRUE/1/'1'
        if (preg_match("/is_active\s*=\s*('1'|TRUE|true|1)/", $whereCopy)) {
            return isset($row['is_active']) && $row['is_active'];
        }

        // Para is_active = FALSE/0/'0'
        if (preg_match("/is_active\s*=\s*('0'|FALSE|false|0)/", $whereCopy)) {
            return isset($row['is_active']) && !$row['is_active'];
        }

        // Soporte para parámetros nombrados :nombre
        foreach ($whereParams as $key => $value) {
            if (strpos($key, ':') === 0) {
                $fieldName = substr($key, 1);
                if (strpos($where, $key) !== false) {
                    return isset($row[$fieldName]) && $row[$fieldName] == $value;
                }
            }
        }

        // Si no se pudo evaluar la condición, retornar false por seguridad
        error_log("JsonDatabase: No se pudo evaluar WHERE clause: $where con params: " . json_encode($whereParams));
        return false;
    }

    private function evaluateCondition($row, $condition) {
        // Para cliente_id = 'valor'
        if (preg_match("/^cliente_id\s*=\s*'?(\d+)'?$/", $condition, $matches)) {
            return isset($row['cliente_id']) && $row['cliente_id'] == $matches[1];
        }

        // Para consulta_id = 'valor'
        if (preg_match("/^consulta_id\s*=\s*'?(\d+)'?$/", $condition, $matches)) {
            return isset($row['consulta_id']) && $row['consulta_id'] == $matches[1];
        }

        // Para es_admin = '0' o '1'
        if (preg_match("/^es_admin\s*=\s*'?([01])'?$/", $condition, $matches)) {
            return isset($row['es_admin']) && $row['es_admin'] == $matches[1];
        }

        // Para leido = '0' o '1'
        if (preg_match("/^leido\s*=\s*'?([01])'?$/", $condition, $matches)) {
            return isset($row['leido']) && $row['leido'] == $matches[1];
        }

        // Para nombre = 'valor'
        if (preg_match("/nombre\s*=\s*'([^']+)'/", $condition, $matches)) {
            return isset($row['nombre']) && $row['nombre'] === $matches[1];
        }

        // Para is_active = '1' o is_active = TRUE
        if (preg_match("/is_active\s*=\s*'?(1|TRUE|true)'?/i", $condition)) {
            return isset($row['is_active']) && $row['is_active'];
        }

        // Para cualquier campo = valor genérico
        if (preg_match("/(\w+)\s*=\s*'([^']+)'/", $condition, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            return isset($row[$field]) && $row[$field] == $value;
        }

        return false;
    }

    private function applyOrderBy($data, $orderBy) {
        // Implementación básica de ORDER BY
        if (strpos($orderBy, 'username') !== false) {
            usort($data, function($a, $b) {
                return strcmp($a['username'] ?? '', $b['username'] ?? '');
            });
        } elseif (strpos($orderBy, 'nombre') !== false) {
            usort($data, function($a, $b) {
                return strcmp($a['nombre'] ?? '', $b['nombre'] ?? '');
            });
        } elseif (strpos($orderBy, 'created_at') !== false) {
            usort($data, function($a, $b) {
                return strcmp($a['created_at'] ?? '', $b['created_at'] ?? '');
            });
        }

        return $data;
    }

    // Métodos específicos para compatibilidad
    public function getDashboardStats() {
        $usuarios = $this->loadTable('usuarios');
        $clientes = $this->loadTable('clientes');
        $documentos = $this->loadTable('documentos');
        $tipos = $this->loadTable('tipos_documento');

        return [
            'usuarios' => count(array_filter($usuarios, function($u) {
                return $u['is_active'] ?? false;
            })),
            'clientes' => count($clientes),
            'documentos' => count($documentos),
            'categorias' => count(array_filter($tipos, function($t) {
                return $t['is_active'] ?? false;
            }))
        ];
    }

    public function getTotalDocumentosPorCategoria() {
        $tipos = $this->loadTable('tipos_documento');
        $documentos = $this->loadTable('documentos');

        // Contar documentos por categoría
        $counts = [];
        foreach ($documentos as $doc) {
            $categoriaId = $doc['categoria_id'] ?? 0;
            $counts[$categoriaId] = ($counts[$categoriaId] ?? 0) + 1;
        }

        // Agregar conteo a tipos de documento
        foreach ($tipos as &$tipo) {
            $tipo['total_documentos'] = $counts[$tipo['id']] ?? 0;
        }

        return array_filter($tipos, function($t) {
            return $t['is_active'] ?? false;
        });
    }

    public function query($sql, $params = []) {
        // Para compatibilidad - no implementado para JSON
        throw new Exception("Raw SQL queries not supported in JSON mode");
    }
}
?>
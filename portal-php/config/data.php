<?php
// Simulamos la "base de datos" con arrays en memoria
// En un entorno real, esto debería estar en archivos JSON o una BD real

class DataStore {
    private static $usuarios = [
        [
            'id' => 1,
            'username' => 'admin',
            'password' => 'admin123', // En la vida real, esto debe estar hasheado
            'email' => 'admin@portal.com',
            'role' => 'admin',
            'is_active' => true
        ],
        [
            'id' => 2,
            'username' => 'cliente1',
            'password' => 'cliente123',
            'email' => 'cliente1@empresa.com',
            'role' => 'cliente',
            'cliente_id' => 1,
            'is_active' => true
        ]
    ];

    private static $clientes = [
        [
            'id' => 1,
            'user_id' => 2,
            'razon_social' => 'Empresa Demo S.A.',
            'rut_empresa' => '123456789',
            'created_at' => '2024-01-01 10:00:00'
        ]
    ];

    private static $tipos_documento = [
        ['id' => 1, 'nombre' => 'Balance General', 'codigo' => 'BAL_GEN'],
        ['id' => 2, 'nombre' => 'Estado de Resultados', 'codigo' => 'EST_RES'],
        ['id' => 3, 'nombre' => 'Flujo de Efectivo', 'codigo' => 'FLU_EFE'],
        ['id' => 4, 'nombre' => 'Declaración de Impuestos', 'codigo' => 'DEC_IMP'],
        ['id' => 5, 'nombre' => 'Análisis Financiero', 'codigo' => 'ANA_FIN']
    ];

    private static $documentos = [
        [
            'id' => 1,
            'cliente_id' => 1,
            'tipo_documento_id' => 1,
            'archivo_consultora' => 'documentos/consultora/2024/01/balance_demo.pdf',
            'archivo_cliente' => null,
            'fecha_actualizacion' => '2024-01-15 10:30:00'
        ],
        [
            'id' => 2,
            'cliente_id' => 1,
            'tipo_documento_id' => 2,
            'archivo_consultora' => null,
            'archivo_cliente' => null,
            'fecha_actualizacion' => '2024-01-10 14:20:00'
        ],
        [
            'id' => 3,
            'cliente_id' => 1,
            'tipo_documento_id' => 3,
            'archivo_consultora' => 'documentos/consultora/2024/01/flujo_demo.pdf',
            'archivo_cliente' => 'documentos/cliente/2024/01/flujo_cliente.pdf',
            'fecha_actualizacion' => '2024-01-20 09:15:00'
        ]
    ];

    // Usuarios
    public static function getUsuarios() {
        return self::$usuarios;
    }

    public static function getUsuarioByUsername($username) {
        foreach (self::$usuarios as $user) {
            if ($user['username'] === $username) {
                return $user;
            }
        }
        return null;
    }

    public static function getUsuarioById($id) {
        foreach (self::$usuarios as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }

    public static function createUsuario($userData) {
        $newId = count(self::$usuarios) + 1;
        $userData['id'] = $newId;
        self::$usuarios[] = $userData;
        return $newId;
    }

    // Clientes
    public static function getClientes() {
        return self::$clientes;
    }

    public static function getClienteByUserId($userId) {
        foreach (self::$clientes as $cliente) {
            if ($cliente['user_id'] == $userId) {
                return $cliente;
            }
        }
        return null;
    }

    public static function createCliente($clienteData) {
        $newId = count(self::$clientes) + 1;
        $clienteData['id'] = $newId;
        $clienteData['created_at'] = date('Y-m-d H:i:s');
        self::$clientes[] = $clienteData;
        return $newId;
    }

    // Tipos de documento
    public static function getTiposDocumento() {
        return self::$tipos_documento;
    }

    public static function getTipoDocumentoById($id) {
        foreach (self::$tipos_documento as $tipo) {
            if ($tipo['id'] == $id) {
                return $tipo;
            }
        }
        return null;
    }

    // Documentos
    public static function getDocumentos() {
        return self::$documentos;
    }

    public static function getDocumentosByClienteId($clienteId) {
        $documentos = [];
        foreach (self::$documentos as $doc) {
            if ($doc['cliente_id'] == $clienteId) {
                // Agregar información del tipo de documento
                $tipoDoc = self::getTipoDocumentoById($doc['tipo_documento_id']);
                $doc['tipo_nombre'] = $tipoDoc['nombre'];
                $doc['tipo_codigo'] = $tipoDoc['codigo'];
                $documentos[] = $doc;
            }
        }
        return $documentos;
    }

    public static function getDocumentoById($id) {
        foreach (self::$documentos as $doc) {
            if ($doc['id'] == $id) {
                return $doc;
            }
        }
        return null;
    }

    public static function updateDocumento($id, $updates) {
        for ($i = 0; $i < count(self::$documentos); $i++) {
            if (self::$documentos[$i]['id'] == $id) {
                self::$documentos[$i] = array_merge(self::$documentos[$i], $updates);
                self::$documentos[$i]['fecha_actualizacion'] = date('Y-m-d H:i:s');
                return true;
            }
        }
        return false;
    }

    // Método para agregar documentos (útil para el admin)
    public static function createDocumento($docData) {
        $newId = count(self::$documentos) + 1;
        $docData['id'] = $newId;
        $docData['fecha_actualizacion'] = date('Y-m-d H:i:s');
        self::$documentos[] = $docData;
        return $newId;
    }
}
?>
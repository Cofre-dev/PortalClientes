<?php
    //router.php
    class Router {
        private $routes = [];

        public function addRoute($method, $pattern, $controller, $action) {
            $this -> routes[] = [
                'method' => strtoupper($method),
                'pattern' => $pattern,
                'controller' => $controller,
                'action' => $action
            ];
        }

        public function get($pattern, $controller, $action) {
            $this->addRoute('GET', $pattern, $controller, $action);
        }

        public function post($pattern, $controller, $action) {
            $this->addRoute('POST', $pattern, $controller, $action);
        }

        public function put($pattern, $controller, $action) {
            $this->addRoute('PUT', $pattern, $controller, $action);
        }

        public function delete($pattern, $controller, $action) {
            $this->addRoute('DELETE', $pattern, $controller, $action);
        }

       public function resolve($uri, $method) {
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['pattern']);
            $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Elimina la coincidencia completa

                $controllerName = $route['controller'];
                $actionName = $route['action'];

                // Extraer el nombre de la clase del controlador (sin .php si existe)
                $controllerClass = str_replace('.php', '', $controllerName);

                // Construir la ruta del archivo del controlador
                $controllerFile = str_ends_with($controllerName, '.php') ? $controllerName : $controllerName . '.php';
                $controllerFilePath = __DIR__ . "/../controllers/{$controllerFile}";

                // Verifica si el archivo del controlador existe
                if (!file_exists($controllerFilePath)) {
                    http_response_code(500);
                    echo json_encode(['error' => "Controller file not found: {$controllerFile}"]);
                    return false;
                }

                // Cargar el archivo del controlador
                require_once $controllerFilePath;

                // Verificar si la clase existe
                if (!class_exists($controllerClass)) {
                    http_response_code(500);
                    echo json_encode(['error' => "Class {$controllerClass} not found after loading file"]);
                    return false;
                }

                // Intentar crear instancia del controlador
                try {
                    $controller = new $controllerClass();
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode([
                        'error' => 'Error creating controller instance',
                        'details' => [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]
                    ]);
                    return false;
                }

                // Verificar si el método existe y ejecutarlo
                if (method_exists($controller, $actionName)) {
                    try {
                        return call_user_func_array([$controller, $actionName], $matches);
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode([
                            'error' => 'Error executing controller method',
                            'details' => [
                                'message' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine()
                            ]
                        ]);
                        return false;
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => "Method '{$actionName}' not found in controller '{$controllerClass}'"]);
                    return false;
                }
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
        return false;
}


        public function renderView($viewPath, $data = []) {
            $title = $data['title'] ?? 'ARA & Bustamante Consultores';
            $favicon = $data['favicon'] ?? '🏢';
            $bodyClass = $data['bodyClass'] ?? '';
            $extraCSS = $data['extraCSS'] ?? '';
            $extraJS = $data['extraJS'] ?? '';

            // Extract data for view
            extract($data);

            // Start output buffering
            ob_start();

            // Include the view file
            include __DIR__ . "/../views/{$viewPath}";

            // Get the view content
            $content = ob_get_clean();

            // Include the layout
            include __DIR__ . '/../views/layouts/main.php';
        }
    }

?>
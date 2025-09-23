<?php

class Router {
    private $routes = [];

    public function addRoute($method, $pattern, $controller, $action) {
        $this->routes[] = [
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
                array_shift($matches); // Remove full match

                $controllerName = $route['controller'];
                $actionName = $route['action'];

                require_once __DIR__ . "/../controllers/{$controllerName}.php";

                $controllerClass = str_replace('.php', '', $controllerName);
                $controller = new $controllerClass();

                if (method_exists($controller, $actionName)) {
                    return call_user_func_array([$controller, $actionName], $matches);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Method not found']);
                    return false;
                }
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
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
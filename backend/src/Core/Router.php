<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    /**
     * Add a GET route
     */
    public function get(string $path, string $controller, string $method): void
    {
        $this->addRoute('GET', $path, $controller, $method);
    }

    /**
     * Add a POST route
     */
    public function post(string $path, string $controller, string $method): void
    {
        $this->addRoute('POST', $path, $controller, $method);
    }

    /**
     * Add a PUT route
     */
    public function put(string $path, string $controller, string $method): void
    {
        $this->addRoute('PUT', $path, $controller, $method);
    }

    /**
     * Add a DELETE route
     */
    public function delete(string $path, string $controller, string $method): void
    {
        $this->addRoute('DELETE', $path, $controller, $method);
    }

    /**
     * Add route to the routes array
     */
    private function addRoute(string $httpMethod, string $path, string $controller, string $method): void
    {
        $this->routes[] = [
            'method' => $httpMethod,
            'path' => $path,
            'controller' => $controller,
            'action' => $method
        ];
    }

    /**
     * Dispatch the request to the appropriate controller
     */
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if needed (e.g., /api)
        $basePath = '';
        if (!empty($basePath)) {
            $requestUri = preg_replace('#^' . $basePath . '#', '', $requestUri);
        }

        foreach ($this->routes as $route) {
            $pattern = $this->convertPathToRegex($route['path']);
            
            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match
                
                $controller = new $route['controller']();
                $action = $route['action'];
                
                echo call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        // No route found
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }

    /**
     * Convert route path to regex pattern
     * Supports parameters like /photos/{id}
     */
    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);
        return '#^' . $pattern . '$#';
    }
}

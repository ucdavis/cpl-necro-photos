<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '')
    {
        // Prefer explicit constructor arg, then APP_BASE_PATH env var
        if ($basePath !== '') {
            $base = $basePath;
        } else {
            $base = $_ENV['APP_BASE_PATH'] ?? getenv('APP_BASE_PATH') ?? '';
        }

        // Normalize: ensure leading slash, no trailing slash (empty if none)
        if ($base !== '') {
            $base = '/' . trim($base, '/');
        }

        $this->basePath = $base;
    }

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
     * Add a PATCH route
     */
    public function patch(string $path, string $controller, string $method): void
    {
        $this->addRoute('PATCH', $path, $controller, $method);
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
        
        // Remove base path if configured (e.g., /necro-photos/api)
        if (!empty($this->basePath) && strpos($requestUri, $this->basePath) === 0) {
            $requestUri = preg_replace('#^' . preg_quote($this->basePath, '#') . '#', '', $requestUri);
            // if request becomes empty, use root
            if ($requestUri === '') {
                $requestUri = '/';
            }
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
        // Match any non-slash sequence for parameters to allow dots in filenames
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}

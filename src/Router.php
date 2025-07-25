<?php

namespace App;

class Router
{
    private $routes = [];

    public function addRoute($method, $path, $callback)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'callback' => $callback
        ];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Pastikan path selalu diawali dengan '/'
        if (empty($path) || substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }

        // Hapus trailing slash agar '/users/' juga cocok dengan '/users'
        if (strlen($path) > 1 && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        // Penanganan OPTIONS method dipindahkan ke index.php untuk efisiensi CORS
        // if ($method === 'OPTIONS') {
        //     http_response_code(200);
        //     exit();
        // }

        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] == $method && preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove the full match

                $callback = $route['callback'];

                if (is_callable($callback)) {
                    call_user_func_array($callback, $matches);
                    return;
                } elseif (is_array($callback) && count($callback) == 2) {
                    $controllerName = "App\\Controllers\\" . $callback[0];
                    $methodName = $callback[1];

                    if (class_exists($controllerName) && method_exists(new $controllerName(), $methodName)) {
                        $controller = new $controllerName();
                        call_user_func_array([$controller, $methodName], $matches);
                        return;
                    }
                }
            }
        }

        http_response_code(404);
        echo json_encode(["message" => "Not Found."]);
    }
}
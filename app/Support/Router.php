<?php

namespace App\Support;

class Router
{
    private static array $routes = [];

    public static function get(string $path, $handler, array $middleware = []): void
    {
        self::add('GET', $path, $handler, $middleware);
    }

    public static function post(string $path, $handler, array $middleware = []): void
    {
        self::add('POST', $path, $handler, $middleware);
    }

    public static function add(string $method, string $path, $handler, array $middleware = []): void
    {
        self::$routes[] = [
            'method' => strtoupper($method),
            'path' => '/' . trim($path, '/'),
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public static function dispatch(string $method, string $uri): void
    {
        $uri = '/' . trim($uri, '/');

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run Middlewares
                foreach ($route['middleware'] as $mw) {
                    self::runMiddleware($mw);
                }

                // Call Handler
                self::callHandler($route['handler'], $params);
                return;
            }
        }

        Response::notFound();
    }

    private static function runMiddleware(string $middleware): void
    {
        $middlewareMap = [
            'auth' => \App\Middleware\AuthMiddleware::class,
            'csrf' => \App\Middleware\CsrfMiddleware::class,
            'teacher' => [\App\Middleware\AuthMiddleware::class, 'teacher'],
            'parent' => [\App\Middleware\AuthMiddleware::class, 'parent'],
            'student' => [\App\Middleware\AuthMiddleware::class, 'student'],
        ];

        if (isset($middlewareMap[$middleware])) {
            $entry = $middlewareMap[$middleware];
            if (is_array($entry)) {
                $class = $entry[0];
                $method = $entry[1];
                (new $class())->$method();
            } else {
                (new $entry())->handle();
            }
        }
    }

    private static function callHandler($handler, array $params = []): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            call_user_func_array([$controller, $method], $params);
            return;
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$class, $method] = explode('@', $handler);
            $fullClass = "App\\Controllers\\" . $class;
            $controller = new $fullClass();
            call_user_func_array([$controller, $method], $params);
            return;
        }

        throw new \RuntimeException("Invalid route handler format.");
    }
}

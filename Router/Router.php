<?php

namespace MA\PHPQUICK\Router;

use MA\PHPQUICK\Router\Route;
use MA\PHPQUICK\Http\Requests\Request;
use MA\PHPQUICK\Exceptions\HttpNotFoundException;

class Router
{
    private array $routes = [];

    public function get(string $path, $callback, ...$middlewares): self
    {
       return $this->register(Request::GET, $path, $callback, $middlewares);
    }

    public function post(string $path, $callback, ...$middlewares): self
    {
       return $this->register(Request::POST, $path, $callback, $middlewares);
    }

    public function put(string $path, $callback, ...$middlewares): self
    {
       return $this->register(Request::PUT, $path, $callback, $middlewares);
    }

    public function patch(string $path, $callback, ...$middlewares): self
    {
       return $this->register(Request::PATCH, $path, $callback, $middlewares);
    }

    public function delete(string $path, $callback, ...$middlewares): self
    {
       return $this->register(Request::DELETE, $path, $callback, $middlewares);
    }

    public function register(string $method, string $path, $callback, array $middlewares): self
    {
        $this->routes[$method][] = [
            'path' => $path,
            'callback' => $callback,
            'middlewares' => $middlewares
        ];

        return $this;
    }

    public function dispatch(string $method, string $path): Route
    {
        $clean = fn($path) => str_replace(['%20', ' '], '-', rtrim($path, '/')) ?: '/';
        foreach ($this->routes[$method] ?? [] as $route) {
            $pattern = '#^' . preg_replace('/:(\w+)/', '(?P<\1>[^/]+)', $clean($route['path'])) . '$#';
            if (preg_match($pattern, $clean($path), $variabels)) {
                $params = array_filter($variabels, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
                return new Route($route['callback'], $route['middlewares'], $params);
            }
        }
        throw new HttpNotFoundException(sprintf('Route Not Found "{ %s }"', $path));
    }
}

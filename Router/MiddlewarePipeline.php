<?php

namespace MA\PHPQUICK\Router;

use MA\PHPQUICK\MVC\View;
use MA\PHPQUICK\Contracts\Middleware;
use MA\PHPQUICK\Contracts\RequestInterface as Request;
use MA\PHPQUICK\Contracts\ResponseInterface as Response;

class MiddlewarePipeline
{
    private int $index = 0;
    private array $middlewares = [];

    public function __construct(array $middlewares, array $mapping = [])
    {
        foreach($middlewares as $middleware){
            $this->middlewares[] = $this->resolveMiddleware($middleware, $mapping);
        }
    }

    private function resolveMiddleware($middleware, array $mapping)
    {
        if (is_string($middleware) && isset($mapping[$middleware])) {
            $middleware = $mapping[$middleware];
        } 
        
        if(is_string($middleware) && class_exists($middleware)) {
            $middleware = new $middleware;
        }

        if ($middleware instanceof Middleware || is_callable($middleware)) {
            return $middleware;
        }
    
        throw new \InvalidArgumentException(
            'The middleware must be an instance of Middleware, a callable, or a valid class name that implements Middleware.'
        );
    }
    

    public function handle(Request $request): Response
    {
        if (!isset($this->middlewares[$this->index])) {
            return response();
        }

        $middleware = $this->middlewares[$this->index];

        $result = $this->executeMiddleware($middleware, $request);

        return $this->createResponse($result);
    }

    private function executeMiddleware($middleware, Request $request)
    {
        if ($middleware instanceof Middleware) {
            return $middleware->execute($request, $this->next());
        }

        return $middleware($request, $this->next());
    }

    private function createResponse($result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }
    
        if ($result instanceof View) {
            return $this->createViewResponse($result);
        }
    
        return response($result);
    }
    
    private function createViewResponse(View $view): Response
    {
        return response($view->render());
    }    

    private function next(): \Closure
    {
        return function (Request $request) {
            $this->index++;
            return $this->handle($request);
        };
    }
}

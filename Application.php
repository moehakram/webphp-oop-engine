<?php
declare(strict_types=1);

namespace MA\PHPQUICK;

use MA\PHPQUICK\MVC\View;
use MA\PHPQUICK\Router\Route;
use MA\PHPQUICK\Router\Router;
use MA\PHPQUICK\Http\Requests\Request;
use MA\PHPQUICK\Http\Responses\Response;
use MA\PHPQUICK\Router\MiddlewarePipeline;
use MA\PHPQUICK\Contracts\RequestInterface as IRequest;
use MA\PHPQUICK\Contracts\ResponseInterface as IResponse;
use MA\PHPQUICK\Contracts\HttpExceptionInterface;
use MA\PHPQUICK\Exceptions\HttpResponseException;

class Application extends Container
{
    public function __construct(
        private readonly IRequest $request,
        private readonly Router $router
    ) {
        static::$instance = $this;
        $this->instance(Request::class, $request);
        $this->instance(IRequest::class, $request);
    }

    public function run() : IResponse
    {
        try {
            $route = $this->router->dispatch($this->request->getMethod(), $this->request->getPath());
            $middlewarePipeline = $this->createMiddlewarePipeline($route);
            return $middlewarePipeline->handle($this->request);
        } catch (HttpResponseException $http) {
            return $http->getResponse();
        } catch (HttpExceptionInterface $httpException) {
            return $this->handleHttpException($httpException);
        }
    }

    private function createMiddlewarePipeline(Route $route): MiddlewarePipeline
    {
        $middlewares = array_merge(
            $this->getGlobalMiddlewares(),
            $route->getMiddlewares(),
            [fn() => $this->executeRouteAction($route)]
        );
        return new MiddlewarePipeline($middlewares, $this->get('middleware.aliases'));
    }

    private function getGlobalMiddlewares(): array
    {
        return $this->has('middleware.global') ? $this->get('middleware.global') : [];
    }

    private function executeRouteAction(Route $route): mixed
    {
        $action = $route->getAction();
        $arguments = $route->getArguments();

        if ($controller = $route->getController()) {
            $controllerInstance = $this->get($controller);

            if (!method_exists($controllerInstance, $action)) {
                throw new \BadMethodCallException("Method {$action} not found in controller {$controller}");
            }

            return $this->call([$controllerInstance, $action], $arguments);
        }

        return $this->call($action, $arguments);
    }

    private function handleHttpException(HttpExceptionInterface $httpException): IResponse
    {
        $handler = $this->getHttpExceptionHandler();

        $content = $handler ? $handler($httpException) : $this->defaultExceptionContent($httpException);
        if ($content instanceof IResponse) return $content;

        if ($content instanceof View) {
            $view = $content->with('message', $httpException->getMessage());
        } else {
            $view = $content ?: $this->defaultExceptionContent($httpException);
        }
        return new Response((string)$view, $httpException->getCode());
    }

    private function getHttpExceptionHandler(): ?callable
    {
        return $this->has('http.exception.handler') ? $this->get('http.exception.handler') : null;
    }

    private function defaultExceptionContent(HttpExceptionInterface $httpException): mixed
    {
        return $this->getOrDefault((string)$httpException->getCode(), $httpException->getMessage());
    }

    private function getOrDefault(string $key, mixed $default): mixed
    {
        return $this->has($key) ? $this->get($key) : $default;
    }
}
<?php

namespace MA\PHPQUICK\Router;

final class Route
{
    private ?string $controller;
    private $action;
    private array $middlewares;
    private array $arguments;

    public function __construct($callback, array $middlewares, array $arguments)
    {
        $this->middlewares = $middlewares;
        $this->arguments = $arguments;
        $this->parseCallback($callback);
    }

    private function parseCallback($callback): void
    {
        if (is_array($callback)) {
            [$this->controller, $this->action] = $this->validateControllerAction($callback);
        } elseif(is_string($callback)){
            $handler = explode('@', $callback);
            [$class, $this->action] = $this->validateControllerAction($handler);
            $this->controller = '\\App\\Controllers\\' . $class;
        } elseif(is_callable($callback)) {
            $this->controller = null;
            $this->action = $callback;
        } else {
            throw new \InvalidArgumentException("Invalid callback provided");
        }
    }

    private function validateControllerAction(array $callback): array
    {
        if (count($callback) !== 2) {
            throw new \InvalidArgumentException('Invalid controller action format ' . json_encode($callback));
        }

        return $callback;
    }

    public function getController(): ?string
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}

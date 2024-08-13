<?php

namespace MA\PHPQUICK\MVC;

use InvalidArgumentException;

class View
{
    private function __construct(
        private string $view,
        private array $data = [],
        private ?string $extend = null
    ) {}

    public static function __callStatic(string $name, array $arguments = []): self
    {
        $data = $arguments ? self::parseArguments($name, $arguments) : [];
        return new static(
            view: str_replace('_', '/', $name),
            data: $data
        );
    }

    public static function make(string $view, array $data = [], ?string $layout = null): self
    {
        return new static(
            view: $view,
            data: $data,
            extend: $layout ? "layouts.$layout" : null
        );
    }

    private static function parseArguments(string $name, array $arguments): array
    {
        if (count($arguments) > 2) {
            throw new InvalidArgumentException("Method '$name' only accepts a maximum of two arguments.");
        }

        return is_array($arguments[0])
            ? $arguments[0]
            : [$arguments[0] => $arguments[1] ?? null];
    }

    public function with(array|string $key, mixed $value = null): self
    {
        $this->data = is_array($key)
            ? array_merge($this->data, $key)
            : [...$this->data, $key => $value];
        return $this;
    }

    public function extend(string $viewName, string $viewPath = 'layouts'): self
    {
        $this->extend = $viewPath . DIRECTORY_SEPARATOR . $viewName;
        return $this;
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function render(): string
    {
        return $this->renderView($this->view, $this->data, $this->extend);
    }

    private function renderView(string $view, array $data = [], ?string $extend = null): string
    {
        try {
            $content = $this->loadView($view, $data);
            return $extend ? $this->renderExtend($content, $data, $extend) : $content;
        } catch (InvalidArgumentException $ex) {
            throw $ex;
        }
    }

    private function loadView(string $view, array $data = []): string
    {
        $viewFilePath = $this->getViewFilePath($view);
        $this->ensureViewFileExists($viewFilePath);
        return $this->renderFile($viewFilePath, $data);
    }

    private function getViewFilePath(string $view): string
    {
        $view = trim($view, '/');
        $viewPath = str_replace('.', DIRECTORY_SEPARATOR, "app.views.$view");
        return base_path("$viewPath.php");
    }

    private function ensureViewFileExists(string $viewFilePath): void
    {
        if (!file_exists($viewFilePath)) {
            throw new InvalidArgumentException("File View '" . basename($viewFilePath) . "' tidak ditemukan di [$viewFilePath]");
        }
    }

    private function renderFile(string $filePath, array $data): string
    {
        extract($data);
        ob_start();
        include $filePath;
        return ob_get_clean();
    }

    private function renderExtend(string $content, array $data, string $extend): string
    {
        $extendContent = $this->loadView($extend, $data);
        return str_replace('{{content}}', $content, $extendContent);
    }
}

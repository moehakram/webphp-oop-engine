<?php

namespace MA\PHPQUICK\MVC;

use MA\PHPQUICK\Application;

abstract class Controller
{
    protected $layout = null;
 
    protected function make(string $key){
        return Application::$instance->get($key);
    }

    protected function view(string $view, array $data = [], ?string $layout = null): View
    {
        return view($view, $data, $layout ?? $this->layout);
    }

    protected function model(string $modelName)
    {
        $modelClass = "\\App\\Models\\" . $modelName;

        $this->checkModelClass($modelClass);

        return new $modelClass;
    }

    private function checkModelClass(string $modelClass)
    {
        if (!class_exists($modelClass)) {
            throw new \Exception(sprintf('{ %s } this model class not found', $modelClass));
        }
    }
}

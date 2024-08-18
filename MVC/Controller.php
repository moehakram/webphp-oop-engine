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
        return View::make($view, $data, $layout ?? $this->layout);
    }
}

<?php

use MA\PHPQUICK\MVC\View;

if (!function_exists('csrf')) {
    function csrf(): string
    {
        session()->set('token', $token = bin2hex(random_bytes(35)));
        return $token;
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = [], ?string $extend = null): View
    {
        return View::make($view, $data, $extend);
    }
}

if (!function_exists('config')) {
    function config($key = null, $default = null)
    {
        return app('config')->getOrSet($key, $default);
    }
}


if (!function_exists('displayAlert')) {
    function displayAlert(array $value): string
    {
        return sprintf(
            '<div class="alert alert-%s">%s</div>',
            $value['type'],
            $value['message']
        );
    }
}
if (!function_exists('inputs')) {
    function inputs($key)
    {
        return session()->getFlash('inputs')[$key] ?? '';
    }
}
if (!function_exists('errors')) {
    function errors($key)
    {
        return session()->getFlash('errors')[$key] ?? '';
    }
}
<?php

use MA\PHPQUICK\Container;
use MA\PHPQUICK\Http\Responses\Response;
use MA\PHPQUICK\Http\Responses\ResponseHeaders;
use MA\PHPQUICK\Contracts\RequestInterface as Request;

if (!function_exists('app')) {

    function app($key = null)
    {
        $app = Container::$instance;

        // Jika ada key, resolve key tersebut dari container
        if ($key) {
            return $app->get($key);
        }

        // Jika tidak ada key, kembalikan instance app
        return $app;
    }
}

if (!function_exists('session')) {

    function session($key = null, $default = null)
    {
        return app('session')->getOrSet($key, $default);
    }
}

if (!function_exists('response')) {
    function response($content = null, $statusCode = 200): Response
    {
        $res = app(Response::class);
        if ($content !== null) {
            $res->setContent($content)->setStatusCode($statusCode);
        }
        return $res;
    }
}

if (!function_exists('request')) {
    function request(): Request
    {
        return app(Request::class);
    }
}

if (!function_exists('headers')) {
    function headers(): ResponseHeaders
    {
        return response()->headers();
    }
}

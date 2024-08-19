<?php

use MA\PHPQUICK\Container;
use MA\PHPQUICK\Http\Responses\Response;
use MA\PHPQUICK\Http\Responses\ResponseHeaders;
use MA\PHPQUICK\Contracts\RequestInterface as Request;

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null  $key
     * @return \MA\PHPQUICK\Contracts\ContainerInterface|mixed
     */
    function app(string $key = null)
    {
        $app = Container::getInstance();

        // Jika tidak ada key, kembalikan instance app
        if (is_null($key)) {
            return $app;
        }

        // Jika ada key, resolve key tersebut dari container
        return $app->get($key);
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

if (!function_exists('db')) {
    /**
     * Get the available Database instance.
     *
     * @param  string|null  $query
     * @param  array|null  $params
     * @return \PDO|\PDOStatement|false
     */
    function db(string $query = null, ?array $params = null)
    {
        if(is_null($query)){
            return app(\PDO::class);
        }
        return app('db')->query($query, $params);
    }
}

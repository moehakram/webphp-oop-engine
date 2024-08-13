<?php

use MA\PHPQUICK\MVC\View;
use MA\PHPQUICK\Container;
use MA\PHPQUICK\Http\Responses\Response;
use MA\PHPQUICK\Http\Responses\ResponseHeaders;
use MA\PHPQUICK\Contracts\RequestInterface as Request;

if (!function_exists('log_exception')) {
    function log_exception(\Throwable $ex): void
    {
        $time = date('Y-m-d H:i:s');
        $message = "[{$time}] Uncaught exception: " . $ex->getMessage() . "\n";
        $message .= "In file: " . $ex->getFile() . " on line " . $ex->getLine() . "\n";
        $message .= "Stack trace:\n" . $ex->getTraceAsString() . "\n";
        // error_log($message, 3, base_path('logs/error.log'));
        error_log($message, 3, config('logging.error_log.path'));
    }
}

if (!function_exists('write_log')) {
    function write_log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] " . (is_array($message) ? json_encode($message) : $message) . PHP_EOL;
        file_put_contents(config('logging.info_log.path'), $logMessage, FILE_APPEND);
    }
}

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

if (!function_exists('strRandom')) {
    function strRandom(int $length = 16): string
    {
        return (function ($length) {
            $string = '';

            while (($len = strlen($string)) < $length) {
                $size = $length - $len;

                $bytesSize = (int) ceil($size / 3) * 3;

                $bytes = random_bytes($bytesSize);

                $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
            }

            return $string;
        })($length);
    }
}

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
if (!function_exists('clean')) {
    function clean($data)
    {
        if (is_string($data)) {
            return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
        }

        if (is_array($data)) {
            return array_map('clean', $data);
        }
        return $data;
    }
}

if (!function_exists('dd')) {
    function dd($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die;
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

function base_path($path = ''): string{
    return dirname(__DIR__) . ($path ? DIRECTORY_SEPARATOR . $path : DIRECTORY_SEPARATOR);
}
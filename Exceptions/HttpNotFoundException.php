<?php
namespace MA\PHPQUICK\Exceptions;

use MA\PHPQUICK\Contracts\HttpExceptionInterface;


class HttpNotFoundException extends \Exception implements HttpExceptionInterface
{
    protected $code = 404;
    protected $message = 'Page Not Found';
}

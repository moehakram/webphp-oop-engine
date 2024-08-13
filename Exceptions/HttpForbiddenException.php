<?php
namespace MA\PHPQUICK\Exceptions;

use MA\PHPQUICK\Contracts\HttpExceptionInterface;


class HttpForbiddenException extends \Exception implements HttpExceptionInterface
{
    protected $code = 403;
    protected $message = 'Forbidden';
}
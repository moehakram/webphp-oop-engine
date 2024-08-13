<?php

namespace MA\PHPQUICK\Exceptions;

use RuntimeException;
use MA\PHPQUICK\Contracts\ResponseInterface as Response;

class HttpResponseException extends RuntimeException
{
    public function __construct(
        protected Response $response
    ){}

    public function getResponse() : Response
    {
        return $this->response;
    }
}
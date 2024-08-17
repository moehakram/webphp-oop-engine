<?php
namespace MA\PHPQUICK\Exceptions;

use MA\PHPQUICK\Contracts\CollectionInterface as Errors;
use MA\PHPQUICK\Contracts\ResponseInterface as Response;

class ValidationException extends \Exception
{
    public $code = 422;
    public $redirectTo;

    public function __construct(
        string $message = "Validation Error",
        private ?Errors $errors = null,
        private ?Response $response = null
        )
    {
        parent::__construct($message);
    }

    public function getErrors(): ?Errors
    {
        return $this->errors;
    }


    public function status(int $status)
    {
        $this->code = $status;

        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function redirectTo($url)
    {
        $this->redirectTo = $url;

        return $this;
    }
    
    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }
}

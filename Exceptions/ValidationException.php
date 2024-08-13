<?php
namespace MA\PHPQUICK\Exceptions;

use MA\PHPQUICK\Collection;
use MA\PHPQUICK\Contracts\ResponseInterface as Response;

class ValidationException extends \Exception
{
    public $code = 422;
    public $redirectTo;

    public function __construct(
        string $message = "Validation Error",
        private ?Collection $errors = null,
        private ?Response $response = null
        )
    {
        parent::__construct($message);
    }

    public function getErrors(): ?Collection
    {
        return $this->errors;
    }

    public function status($status)
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
}

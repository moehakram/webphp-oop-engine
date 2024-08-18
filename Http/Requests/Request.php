<?php
namespace MA\PHPQUICK\Http\Requests;

use MA\PHPQUICK\Collection;
use MA\PHPQUICK\Session\Session;
use MA\PHPQUICK\Contracts\Authenticable;
use MA\PHPQUICK\Contracts\RequestInterface as IRequest;

class Request implements IRequest
{
    // http method
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';

    private const VALID_METHODS = [
        self::GET,
        self::POST,
        self::PUT,
        self::PATCH,
        self::DELETE
    ];

    private string $path;
    private string $method;

    private Collection $query;
    private Collection $post;
    private Collection $put;
    private Collection $patch;
    private Collection $delete;
    private Collection $server;
    private Collection $cookies;
    private ?Collection $originalMethodCollection = null;
    private Files $files;
    private RequestHeaders $headers;
    private string $previousUrl = '';
    private ?string $rawBody = null;
    private ?Authenticable $user = null;

    public function __construct() {
        $this->initializeServerAndHeaders();
        $this->initializeCollection();
        $this->setPath();
        $this->setMethod();
        $this->setUnsupportedMethodsCollections();
    }

    private function initializeServerAndHeaders(){
        $server = $_SERVER;

        foreach (['HTTP_CONTENT_LENGTH' => 'CONTENT_LENGTH', 'HTTP_CONTENT_TYPE' => 'CONTENT_TYPE'] as $httpKey => $serverKey) {
            if (isset($server[$httpKey])) {
                $server[$serverKey] = $server[$httpKey];
            }
        }

        $this->server = new Collection($server);
        $this->headers = new RequestHeaders($server);
    }

    private function initializeCollection(){
        $this->query = new Collection($_GET);
        $this->post = new Collection($_POST);
        $this->put = new Collection();
        $this->patch = new Collection();
        $this->delete = new Collection();
        $this->files = new Files($_FILES);
        $this->cookies = new Collection($_COOKIE);
    }

    public function cookies() : Collection
    {
        return $this->cookies;
    }

    public function files() : Files
    {
        return $this->files;
    }

    public function post($name = null, $default = null): mixed
    {
        if (is_null($name)) {
            return $this->post->getAll();
        }
        return $this->post->get($name, $default);
    }

    public function query($name = null, $default = null): mixed
    {
        if (is_null($name)) {
            return $this->query->getAll();
        }
        return $this->query->get($name, $default);
    }

    public function get(string $name, $default = null): mixed
    {
        return $this->input($name, $default);
    }

    public function __get(string $name): mixed
    {
        return $this->input($name);
    }

    public function input(string $name, $default = null)
    {
        if ($this->isJson()) {
            return $this->getJsonBody()[$name] ?? $default;
        }

        return match ($this->method) {
            Request::GET => $this->query->get($name, $default),
            Request::POST => $this->post->get($name, $default),
            Request::DELETE => $this->delete->get($name, $default),
            Request::PUT => $this->put->get($name, $default),
            Request::PATCH => $this->patch->get($name, $default),
            default => $this->query->get($name, $default),
        };
    }

    public function headers() : RequestHeaders
    {
        return $this->headers;
    }


    public function getJsonBody() : array
    {
        $json = json_decode($this->getRawBody(), true);

        if ($json === null) {
            throw new \RuntimeException('Body could not be decoded as JSON');
        }

        return $json;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getPreviousUrl() : string
    {
        return $this->previousUrl ?: $this->headers->get('REFERER', '');
    }

    public function getRawBody() : string
    {
        return $this->rawBody ??= file_get_contents('php://input');
    }

    public function getServer() : Collection
    {
        return $this->server;
    }

    public function getUser()
    {
        return $this->server->get('PHP_AUTH_USER');
    }

    public function isAjax() : bool
    {
        return $this->headers->get('X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    public function isJson() : bool
    {
        return preg_match("/application\/json/i", $this->headers->get('CONTENT_TYPE') ?? '') === 1;
    }

    public function setMethod(string $method = null)
    {
        $method = $method ?? $this->server->get('REQUEST_METHOD', Request::GET);

        if ($method == Request::POST) {

            $method = $this->server->get('X-HTTP-METHOD-OVERRIDE') 
                ?? $this->post->get('_method') 
                ?? $this->query->get('_method')
                ?? $method;

            $this->originalMethodCollection = $this->post;
        }
        
        $this->validateAndSetMethod($method);
    }

    private function validateAndSetMethod(string $method): void
    {
        $method = strtoupper($method);

        if (!in_array($method, self::VALID_METHODS)) {
            throw new \InvalidArgumentException("Invalid HTTP method \"$method\"");
        }

        $this->method = $method;
    }

    public function setPath(string $path = null)
    {
        $this->path = $path ?? $this->server->get('PATH_INFO', '/');
    }

    public function setPreviousUrl(string $previousUrl)
    {
        $this->previousUrl = $previousUrl;
    }


    private function isUnsupportedMethod(): bool
    {
        return (mb_strpos($this->headers->get('CONTENT_TYPE', ''), 'application/x-www-form-urlencoded') === 0
                || mb_strpos($this->headers->get('CONTENT_TYPE', ''), 'multipart/form-data') === 0) 
                && in_array($this->method, [Request::PUT, Request::PATCH, Request::DELETE]);
    }

    private function setUnsupportedMethodsCollections()
    {
        if ($this->isUnsupportedMethod()){
            if ($this->originalMethodCollection === null) {
                parse_str($this->getRawBody(), $collection);
            } else {
                $collection = $this->originalMethodCollection->getAll();
            }

            match ($this->method) {
                Request::PUT => $this->put->exchangeArray($collection),
                Request::PATCH => $this->patch->exchangeArray($collection),
                Request::DELETE => $this->delete->exchangeArray($collection)
            };
        }
    }
    
    public function login(?Authenticable $user){
        $this->user = $user;
    }

    public function user(): ?Authenticable{
        return $this->user;
    }

    public function session(): Session
    {
        return session();
    }
}
<?php
namespace MA\PHPQUICK\Http\Responses;

class Cookie
{
    private $name = '';
    private $value = '';
    private $expiration = null;
    private $path = '/';
    private $domain = '';
    private $isSecure = false;
    private $isHttpOnly = true;
    private $sameSite;

    public function __construct(
        string $name,
        $value,
        $expiration,
        string $path = '/',
        string $domain = '',
        bool $isSecure = false,
        bool $isHttpOnly = true,
        string $sameSite = null
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->setExpiration($expiration);
        $this->path = $path;
        $this->domain = $domain;
        $this->isSecure = $isSecure;
        $this->isHttpOnly = $isHttpOnly;
        $this->sameSite = $sameSite;
    }

    public function getDomain() : string
    {
        return $this->domain;
    }

    public function getExpiration() : int
    {
        return $this->expiration;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getSameSite()
    {
        return $this->sameSite;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isHttpOnly() : bool
    {
        return $this->isHttpOnly;
    }

    public function isSecure() : bool
    {
        return $this->isSecure;
    }

    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }

    public function setExpiration($expiration)
    {
        if ($expiration instanceof \DateTime) {
            $expiration = (int)$expiration->format('U');
        }

        $this->expiration = $expiration;
    }

    public function setHttpOnly(bool $isHttpOnly)
    {
        $this->isHttpOnly = $isHttpOnly;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function setSameSite($sameSite)
    {
        $this->sameSite = $sameSite;
    }

    public function setSecure(bool $isSecure)
    {
        $this->isSecure = $isSecure;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
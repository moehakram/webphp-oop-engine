<?php
namespace MA\PHPQUICK\Contracts;

use MA\PHPQUICK\Collection;
use MA\PHPQUICK\Session\Session;
use MA\PHPQUICK\Http\Requests\Files;
use MA\PHPQUICK\Contracts\Authenticable;
use MA\PHPQUICK\Http\Requests\RequestHeaders;

interface RequestInterface
{
    
    public static function setTrustedHeaderName(string $name, $value);

    public static function setTrustedProxies($trustedProxies);

    public function getClientIPAddress() : string;

    public function cookies() : Collection;

    public function files() : Files;

    public function post($key = null, $default = null);

    public function query($key = null, $default = null);

    public function getFullUrl() : string;

    public function headers() : RequestHeaders;
    
    public function getHost() : string;

    public function input(string $name, $default = null);

    public function getJsonBody() : array;

    public function getMethod() : string;

    public function getPath() : string;

    public function getPort() : int;

    public function getPreviousUrl() : string;

    public function getRawBody() : string;

    public function getServer() : Collection;

    public function getUser();

    public function isAjax() : bool;

    public function isJson() : bool;

    public function isPath(string $path, bool $isRegex = false) : bool;

    public function isSecure() : bool;

    public function isUrl(string $url, bool $isRegex = false) : bool;

    public function setMethod(string $method = null);

    public function setPath(string $path = null);

    public function setPreviousUrl(string $previousUrl);

    public function login(?Authenticable $user);

    public function user(): ?Authenticable;

    public function session(): Session;
}
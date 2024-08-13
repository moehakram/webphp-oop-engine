<?php
namespace MA\PHPQUICK\Http\Requests;

use MA\PHPQUICK\Collection;
use MA\PHPQUICK\Session\Session;
use MA\PHPQUICK\Http\Requests\Files;
use MA\PHPQUICK\Contracts\Authenticable;
use MA\PHPQUICK\Http\Requests\RequestHeaders;
use MA\PHPQUICK\Contracts\RequestInterface as IRequest;

class Request implements IRequest
{
    // http method
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';

    private const VALIDMETHODS = [
        self::GET,
        self::POST,
        self::PUT,
        self::PATCH,
        self::DELETE
    ];
    
    private static $trustedProxies = [];
    private static $trustedHeaderNames = [
        RequestHeaders::FORWARDED => 'FORWARDED',
        RequestHeaders::CLIENT_IP => 'X_FORWARDED_FOR',
        RequestHeaders::CLIENT_HOST => 'X_FORWARDED_HOST',
        RequestHeaders::CLIENT_PORT => 'X_FORWARDED_PORT',
        RequestHeaders::CLIENT_PROTO => 'X_FORWARDED_PROTO'
    ];

    private string $path;
    private string $method;
    private array $clientIPAddresses = [];

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
        $this->setClientIPAddresses();
        $this->setUnsupportedMethodsCollections();
    }

    private function initializeServerAndHeaders(){
        $server = $_SERVER;
        if (array_key_exists('HTTP_CONTENT_LENGTH', $server)) {
            $server['CONTENT_LENGTH'] = $server['HTTP_CONTENT_LENGTH'];
        }

        if (array_key_exists('HTTP_CONTENT_TYPE', $server)) {
            $server['CONTENT_TYPE'] = $server['HTTP_CONTENT_TYPE'];
        }
        $this->server = new Collection($server);
        $this->headers = new RequestHeaders($server);
    }

    private function initializeCollection(){
        $this->query = new Collection($_GET);
        $this->post = new Collection($_POST);
        $this->put = new Collection([]);
        $this->patch = new Collection([]);
        $this->delete = new Collection([]);
        $this->files = new Files($_FILES);
        $this->cookies = new Collection($_COOKIE);
    }

    public static function setTrustedHeaderName(string $name, $value)
    {
        self::$trustedHeaderNames[$name] = $value;
    }

    public static function setTrustedProxies($trustedProxies)
    {
        self::$trustedProxies = (array)$trustedProxies;
    }

    public function getClientIPAddress() : string
    {
        return $this->clientIPAddresses[0];
    }

    public function cookies() : Collection
    {
        return $this->cookies;
    }

    public function files() : Files
    {
        return $this->files;
    }

    public function post($name = null, $default = null) : null|array|string
    {
        if (is_null($name)) {
            return $this->post->getAll();
        }
        return $this->post->get($name, $default);
    }

    public function query($name = null, $default = null) : null|array|string
    {
        if (is_null($name)) {
            return $this->query->getAll();
        }
        return $this->query->get($name, $default);
    }

    public function input(string $name, $default = null)
    {
        if ($this->isJson()) {
            $json = $this->getJsonBody();

            if (array_key_exists($name, $json)) {
                return $json[$name];
            } else {
                return $default;
            }
        } else {
            $value = null;

            switch ($this->method) {
                case Request::GET:
                    return $this->query->get($name, $default);
                case Request::POST:
                    $value = $this->post->get($name, $default);
                    break;
                case Request::DELETE:
                    $value = $this->delete->get($name, $default);
                    break;
                case Request::PUT:
                    $value = $this->put->get($name, $default);
                    break;
                case Request::PATCH:
                    $value = $this->patch->get($name, $default);
                    break;
            }

            if ($value === null) {
                $value = $this->query->get($name, $default);
            }

            return $value;
        }
    }

    public function getFullUrl() : string
    {
        $isSecure = $this->isSecure();
        $rawProtocol = strtolower($this->server->get('SERVER_PROTOCOL'));
        $parsedProtocol = substr($rawProtocol, 0, strpos($rawProtocol, '/')) . ($isSecure ? 's' : '');
        $port = $this->getPort();
        $host = $this->getHost();

        // Prepend a colon if the port is non-standard
        if ((!$isSecure && $port != '80') || ($isSecure && $port != '443')) {
            $port = ":$port";
        } else {
            $port = '';
        }

        return $parsedProtocol . '://' . $host . $port . $this->server->get('REQUEST_URI');
    }

    public function headers() : RequestHeaders
    {
        return $this->headers;
    }

    public function getHost() : string
    {
        $host = null;

        if ($this->isUsingTrustedProxy() && $this->headers->has(self::$trustedHeaderNames[RequestHeaders::CLIENT_HOST])) {
            $hosts = explode(',', $this->headers->get(self::$trustedHeaderNames[RequestHeaders::CLIENT_HOST]));
            $host = trim(end($hosts));
        }

        if ($host === null) {
            $host = $this->headers->get('HOST');
        }

        if ($host === null) {
            $host = $this->server->get('SERVER_NAME');
        }

        if ($host === null) {
            // Return an empty string by default so we can do string operations on it later
            $host = $this->server->get('SERVER_ADDR', '');
        }

        // Remove the port number
        $host = strtolower(preg_replace("/:\d+$/", '', trim($host)));

        // Check for forbidden characters
        // Credit: Symfony HTTPFoundation
        if (!empty($host) && !empty(preg_replace("/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/", '', $host))) {
            throw new \InvalidArgumentException("Invalid host \"$host\"");
        }

        return $host;
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

    public function getPort() : int
    {
        if ($this->isUsingTrustedProxy()) {
            if ($this->server->has(self::$trustedHeaderNames[RequestHeaders::CLIENT_PORT])) {
                return (int)$this->server->get(self::$trustedHeaderNames[RequestHeaders::CLIENT_PORT]);
            } elseif ($this->server->get(self::$trustedHeaderNames[RequestHeaders::CLIENT_PROTO]) === 'https') {
                return 443;
            }
        }

        return (int)$this->server->get('SERVER_PORT');
    }

    public function getPreviousUrl() : string
    {
        if (!empty($this->previousUrl)) {
            return $this->previousUrl;
        }       
        return $this->headers->get('REFERER', '');
    }

    public function getRawBody() : string
    {
        if ($this->rawBody === null) {
            $this->rawBody = file_get_contents('php://input');
        }

        return $this->rawBody;
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

    public function isPath(string $path, bool $isRegex = false) : bool
    {
        if ($isRegex) {
            return preg_match('#^' . $path . '$#', $this->path) === 1;
        } else {
            return $this->path == $path;
        }
    }

    public function isSecure() : bool
    {
        if ($this->isUsingTrustedProxy() && $this->server->has(self::$trustedHeaderNames[RequestHeaders::CLIENT_PROTO])) {
            $protoString = $this->server->get(self::$trustedHeaderNames[RequestHeaders::CLIENT_PROTO]);
            $protoArray = explode(',', $protoString);

            return count($protoArray) > 0 && in_array(strtolower($protoArray[0]), ['https', 'ssl', 'on']);
        }

        return $this->server->has('HTTPS') && $this->server->get('HTTPS') !== 'off';
    }

    public function isUrl(string $url, bool $isRegex = false) : bool
    {
        if ($isRegex) {
            return preg_match('#^' . $url . '$#', $this->getFullUrl()) === 1;
        } else {
            return $this->getFullUrl() == $url;
        }
    }

    public function setMethod(string $method = null)
    {
        if ($method === null) {
            $method = $this->server->get('REQUEST_METHOD', Request::GET);

            if ($method == Request::POST) {
                if (($overrideMethod = $this->server->get('X-HTTP-METHOD-OVERRIDE')) !== null) {
                    $method = $overrideMethod;
                } elseif (($overrideMethod = $this->post->get('_method')) !== null) {
                    $method = $overrideMethod;
                } elseif (($overrideMethod = $this->query->get('_method')) !== null) {
                    $method = $overrideMethod;
                }

                $this->originalMethodCollection = $this->post;
            }
        }

        if (!is_string($method)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'HTTP method must be string, %s provided',
                    is_object($method) ? get_class($method) : gettype($method)
                )
            );
        }

        $method = strtoupper($method);

        if (!in_array($method, self::VALIDMETHODS)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid HTTP method "%s"',
                    $method
                )
            );
        }

        $this->method = $method;
    }

    public function setPath(string $path = null)
    {
        if ($path === null) {
            $this->path = $this->server->get('PATH_INFO', '/');
        } else {
            $this->path = $path;
        }
    }

    public function setPreviousUrl(string $previousUrl)
    {
        $this->previousUrl = $previousUrl;
    }

    private function isUsingTrustedProxy() : bool
    {
        return in_array($this->server->get('REMOTE_ADDR'), self::$trustedProxies);
    }

    private function setClientIPAddresses()
    {
        if ($this->isUsingTrustedProxy()) {
            $this->clientIPAddresses = [$this->server->get('REMOTE_ADDR')];
        } else {
            $ipAddresses = [];

            // RFC 7239
            if ($this->headers->has(self::$trustedHeaderNames[RequestHeaders::FORWARDED])) {
                $header = $this->headers->get(self::$trustedHeaderNames[RequestHeaders::FORWARDED]);
                preg_match_all("/for=(?:\"?\[?)([a-z0-9:\.\-\/_]*)/", $header, $matches);
                $ipAddresses = $matches[1];
            } elseif ($this->headers->has(self::$trustedHeaderNames[RequestHeaders::CLIENT_IP])) {
                $ipAddresses = explode(',', $this->headers->get(self::$trustedHeaderNames[RequestHeaders::CLIENT_IP]));
                $ipAddresses = array_map('trim', $ipAddresses);
            }

            $ipAddresses[] = $this->server->get('REMOTE_ADDR');
            $fallbackIpAddresses = [$ipAddresses[0]];

            foreach ($ipAddresses as $index => $ipAddress) {
                if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                    unset($ipAddresses[$index]);
                }

                if (in_array($ipAddress, self::$trustedProxies)) {
                    unset($ipAddresses[$index]);
                }
            }

            $this->clientIPAddresses = count($ipAddresses) === 0 ? $fallbackIpAddresses : array_reverse($ipAddresses);
        }
    }

    private function setUnsupportedMethodsCollections()
    {

        if ((mb_strpos($this->headers->get('CONTENT_TYPE') ?? '', 'application/x-www-form-urlencoded') === 0
                || mb_strpos($this->headers->get('CONTENT_TYPE') ?? '', 'multipart/form-data') === 0) &&
            in_array($this->method, [Request::PUT, Request::PATCH, Request::DELETE])){
            if ($this->originalMethodCollection === null) {
                parse_str($this->getRawBody(), $collection);
            } else {
                $collection = $this->originalMethodCollection->getAll();
            }

            switch ($this->method) {
                case Request::PUT:
                    $this->put->exchangeArray($collection);
                    break;
                case Request::PATCH:
                    $this->patch->exchangeArray($collection);
                    break;
                case Request::DELETE:
                    $this->delete->exchangeArray($collection);
                    break;
            }
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
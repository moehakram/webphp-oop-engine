<?php
namespace MA\PHPQUICK\Http\Responses;

use DateTime;
use MA\PHPQUICK\Exceptions\HttpNotFoundException;
use MA\PHPQUICK\Exceptions\HttpForbiddenException;
use MA\PHPQUICK\Contracts\ResponseInterface as IResponse;

class Response implements IResponse
{
    protected $content;
    protected int $statusCode;
    protected ?string $statusText;
    protected string $httpVersion = '1.1';
    protected ResponseHeaders $headers;
    
    public function __construct($content = '', int $statusCode = 200, array $headers = [])
    {
        $this->setContent($content);
        $this->setStatusCode($statusCode);
        $this->headers = new ResponseHeaders($headers);
    }

    public function setNoCache() : self
    {
        $this->headers->add('Cache-Control','no-store, no-cache, must-revalidate, max-age=0');
        $this->headers->add('Pragma', 'no-cache');
        $this->setExpiration(new DateTime('Sat, 26 Jul 1997 05:00:00 GMT'));
        return $this;
    }

    public function setNotFound()
    {
        throw new HttpNotFoundException();
    }
    
    public function setForbidden()
    {
        throw new HttpForbiddenException();
    }

    public function back(){
        return $this->redirect(request()->getPreviousUrl());
    }

    public function redirect(string $targetUrl, int $statusCode = 302): RedirectResponse
    {
        $response = new RedirectResponse($targetUrl, $statusCode, $this->headers->getAll());
        $response->headers()->setCookies($this->headers->getCookies(true));
        return $response;
    }

    final public function getContent() : string
    {
        return $this->content;
    }

    public function headers() : ResponseHeaders
    {
        return $this->headers;
    }

    final public function getHttpVersion() : string
    {
        return $this->httpVersion;
    }

    final public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    final public function send(): void
    {
        if (!headers_sent()) {
            $this->sendHeaders();
        }
        $this->sendContent();
    }

    final protected function sendContent(): void
    {
        echo $this->content;
    }

    final protected function sendHeaders()
    { 
        header(
            sprintf(
                'HTTP/%s %s %s',
                $this->httpVersion,
                $this->statusCode,
                $this->statusText
            ),
            true,
            $this->statusCode
        );

        foreach ($this->headers->getAll() as $name => $values) {
            foreach ($values as $value) {
                header("$name:$value", false, $this->statusCode);
            }
        }

        foreach ($this->headers->getCookies(true) as $cookie) {
            $options = [
                'expires' => $cookie->getExpiration(),
                'path' => $cookie->getPath(),
                'domain' => $cookie->getDomain(),
                'secure' => $cookie->isSecure(),
                'httponly' => $cookie->isHttpOnly(),
            ];

            if (!$sameSite = $cookie->getSameSite()) {
                $options['samesite'] = $sameSite;
            }

            setcookie($cookie->getName(), $cookie->getValue(), $options);
        }
    }

    public function setContent($content) : self
    {
        $this->content = $content;
        return $this;
    }

    public function setHttpVersion(string $httpVersion): self
    {
        $this->httpVersion = $httpVersion;
        return $this;
    }

    final public function setStatusCode(int $statusCode, string $statusText = null): self
    {
        $this->statusCode = $statusCode;

        if ($statusText === null && isset(ResponseHeaders::STATUS_TEXTS[$statusCode])) {
            $this->statusText = ResponseHeaders::STATUS_TEXTS[$statusCode];
        } else {
            $this->statusText = $statusText;
        }
        return $this;
    }

    
    public function setExpiration(\DateTime $expiration): self
    {
        $this->headers->set('Expires', $expiration->format('r'));
        return $this;
    }
}
<?php
namespace MA\PHPQUICK\Session;

use MA\PHPQUICK\Collection;
use MA\PHPQUICK\Http\Responses\Cookie;
use MA\PHPQUICK\Traits\Token;

class CookieSession extends Collection
{
    use Token;
    private string $cookieName;
    private int $expiration;

    public function __construct(string $cookieName, string $secretToken, int $expiration = 3600)
    {
        $this->cookieName = $cookieName;
        self::$secretToken = $secretToken;
        $this->expiration = $expiration;

        // $token = request()->cookies()->get($this->cookieName);
        $token = filter_input(INPUT_COOKIE, $this->cookieName);
        if ($token) {
            $this->verifyToken($token, $this);
        }
    }

    public function clear(): self
    {
        parent::clear();
        headers()->deleteCookie($this->cookieName);
        return $this;
    }

    public function push(): void
    {
        $token = $this->generateToken($this->getAll());
        response()->headers()->setCookie(new Cookie(
            $this->cookieName,
            $token,
            time() + $this->expiration,
            '/',
            '',
            false,
            true
        ));
    }
}

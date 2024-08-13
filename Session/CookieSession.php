<?php
namespace MA\PHPQUICK\Session;

use MA\PHPQUICK\Token;
use MA\PHPQUICK\Collection;
use MA\PHPQUICK\Http\Responses\Cookie;

class CookieSession extends Collection
{
    use Token;
    private string $cookie_name;
    private int $expiration;

    public function __construct(string $cookie_name, string $secretToken, int $expiration = 3600)
    {
        $this->cookie_name = $cookie_name;
        self::$secretToken = $secretToken;
        $this->expiration = $expiration;

        // $token = request()->cookies()->get($this->cookie_name);
        $token = filter_input(INPUT_COOKIE, $this->cookie_name);
        if ($token) {
            $this->verifyToken($token, $this);
        }
    }

    public function clear(): void
    {
        parent::clear();
        headers()->deleteCookie($this->cookie_name);
    }

    public function push(): void
    {
        $token = $this->generateToken($this->getAll());
        response()->headers()->setCookie(new Cookie(
            $this->cookie_name,
            $token,
            time() + $this->expiration,
            '/',
            '',
            false,
            true
        ));
    }
}

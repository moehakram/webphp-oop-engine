<?php

namespace MA\PHPQUICK\Traits;

use Firebase\JWT\{JWT, Key};
use MA\PHPQUICK\Collection;

trait Token
{
    private static $ALGORITHM = 'HS256';
    protected static string $secretToken;

    protected function generateToken(array $payload): string
    {
        return JWT::encode($payload, self::$secretToken, self::$ALGORITHM);
    }

    protected function verifyToken(string $token, Collection $collection): bool
    {
        try {
            $data = (array) JWT::decode($token, new Key(self::$secretToken, self::$ALGORITHM));
            $collection->exchangeArray($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

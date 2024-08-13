<?php
namespace MA\PHPQUICK\Http\Requests;

use MA\PHPQUICK\Http\Headers;

class RequestHeaders extends Headers
{
    const CLIENT_HOST = 'client-host';
    const CLIENT_IP = 'client-ip';
    const CLIENT_PORT = 'client-port';
    const CLIENT_PROTO = 'client-proto';
    const FORWARDED = 'forwarded';
    
    protected static $specialCaseHeaders = [
        'AUTH_TYPE' => true,
        'CONTENT_LENGTH' => true,
        'CONTENT_TYPE' => true,
        'PHP_AUTH_DIGEST' => true,
        'PHP_AUTH_PW' => true,
        'PHP_AUTH_TYPE' => true,
        'PHP_AUTH_USER' => true
    ];

    public function __construct(array $values = [])
    {
        foreach ($values as $name => $value) {
            $name = strtoupper($name);

            if (isset(self::$specialCaseHeaders[$name]) || strpos($name, 'HTTP_') === 0) {
                $this->set($name, $value);
            }
        }
    }

    protected function normalizeName(string $name) : string
    {
        $name = parent::normalizeName($name);

        if (strpos($name, 'http-') === 0) {
            $name = substr($name, 5);
        }

        return $name;
    }
}
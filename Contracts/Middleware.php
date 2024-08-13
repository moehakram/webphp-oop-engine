<?php
namespace MA\PHPQUICK\Contracts;

interface Middleware
{
    public function execute(RequestInterface $request, \Closure $next);
}

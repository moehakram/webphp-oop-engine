<?php

test("dsn-config", function(){

    $config = [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'php_mvc',
        'charset' => 'utf8mb4',
    ];

    $dsn = 'mysql:'. http_build_query($config, '', ';');

    expect($dsn)->toEqual('mysql:host=localhost;port=3306;dbname=php_mvc;charset=utf8mb4');
});

function isPath(string $path, bool $isRegex = false) : bool
{
    if ($isRegex) {
        return preg_match('#^' . $path . '$#', '/users/login') === 1;
    } else {
        return hash_equals('/users/login', $path);
    }
}

it('is-path', function(){

    $result1 = isPath('/user/login');
    expect($result1)->toBeFalse();

    $result2 = isPath('/users/login/', true);
    expect($result2)->toBeTrue();

});


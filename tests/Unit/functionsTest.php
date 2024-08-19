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


it('is-path', function(){

    $result1 = isPath('/users/login', $param);
    expect($result1)->toBeFalse();

    $result2 = isPath('/users/login/123', $param);
    expect($result2)->toBeTrue();
});

it('pattern', function(string $path){
    $result2 = isPath($path, $param);
    expect($result2)->toBeTrue();
})->with(
    ['path' => '/users/login/:id']
);

it('pattern2', function(string $path){
    $result2 = isPath($path, $param);
    expect($result2)->toBeTrue();
    expect($param)->toEqual([]);
    echo print_r($param);
})->with(['/users/login/123']);

it('pattern3', function(string $path){
    $result2 = isPath($path, $param);
    expect($result2)->toBeTrue();
    expect($param)->toEqual(['id' => 123]);
    echo print_r($param);
})->with(['/users/login/:id']);

function isPath(string $path, &$variabels) : bool
{
    $pathInfo = '/users/login/123';
    $pattern = '#^' . preg_replace('/:(\w+)/', '(?P<$1>[^/]+)', $path) . '$#';
    $result = preg_match($pattern, $pathInfo, $variabels) === 1;
    $variabels = array_filter($variabels, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
    return $result;
}
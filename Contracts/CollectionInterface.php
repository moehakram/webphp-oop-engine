<?php
namespace MA\PHPQUICK\Contracts;


interface CollectionInterface extends \IteratorAggregate, \Countable, \ArrayAccess
{
    public function getAll(): array;

    public function get(string $key, $default = null);

    public function set(string $key, $value): void;

    public function exchangeArray(array $array): array;

    public function remove(string $key): void;

    public function has(string $key): bool;

    public function clear(): void;

    public function add($key, $value = null): void;

    public function getOrSet($key = null, $default = null);
}
<?php
namespace MA\PHPQUICK\Contracts;


interface CollectionInterface extends \IteratorAggregate, \Countable, \ArrayAccess
{
    public function getAll(): array;

    public function toArray(): array;

    public function get(string $key, $default = null);

    public function set(string $key, $value): self;

    public function exchangeArray(array $array): array;

    public function remove(string $key): self;

    public function has(string $key): bool;

    public function clear(): self;

    public function add($key, $value = null): self;

    public function getOrSet($key = null, $default = null);
}
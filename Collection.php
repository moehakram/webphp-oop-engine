<?php
declare(strict_types=1);

namespace MA\PHPQUICK;

use MA\PHPQUICK\Contracts\CollectionInterface;

class Collection implements CollectionInterface
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function getAll(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function get(string $key, $default = null)
    {
        return $this->items[$key] ?? $default;
    }

    public function set(string $key, $value): static
    {
        $this->items[$key] = $value;
        return $this;
    }

    public function exchangeArray(array $array): array
    {
        $oldValues = $this->items;
        $this->items = $array;

        return $oldValues;
    }

    public function remove(string $key): static
    {
        unset($this->items[$key]);
        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function clear(): static
    {
        $this->items = [];
        return $this;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
       $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    public function add($key, $value = null): static
    {
        $keys = is_array($key) ? $key : [$key => $value];
        
        foreach ($keys as $k => $v) {
            $this->items[$k] = $v;
        }
        return $this;
    }

    public function getOrSet($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this;
        }

        if (is_array($key)) {
            return $this->add($key);
        }

        return $this->get($key, $default);
    }

    public function __get($name)
    {
        return $this->get($name);
    }
}

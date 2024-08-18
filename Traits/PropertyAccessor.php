<?php

namespace MA\PHPQUICK\Traits;

trait PropertyAccessor
{
    public function has(string $key): bool
    {
        return isset($this->$key);
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->$key ?? $default;
    }

    public function set(string $key, $value): self
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
        return $this;
    }
}
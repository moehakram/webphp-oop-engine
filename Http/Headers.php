<?php

namespace MA\PHPQUICK\Http;

use MA\PHPQUICK\Collection;

class Headers extends Collection
{

    public function add($key, $values = null, bool $shouldReplace = true): self
    {
        $keys = is_array($key) ? $key : [$key => $values];
        
        foreach ($keys as $name => $value) {
            $this->set($name, $value, $shouldReplace);
        }
        return $this;
    }

    public function get(string $name, $default = null, bool $onlyReturnFirst = true)
    {
        if ($this->has($name)) {
            $value = $this->items[$this->normalizeName($name)];

            if ($onlyReturnFirst) {
                return $value[0];
            }
        } else {
            $value = $default;
        }

        return $value;
    }

    public function has(string $name) : bool
    {
        return parent::has($this->normalizeName($name));
    }

    public function remove(string $name): self
    {
        return parent::remove($this->normalizeName($name));
    }

    public function set(string $name, $values, bool $shouldReplace = true): self
    {
        $name = $this->normalizeName($name);
        $values = (array)$values;

        if ($shouldReplace || !$this->has($name)) {
            parent::set($name, $values);
        } else {
            parent::set($name, array_merge($this->items[$name], $values));
        }
        return $this;
    }

    protected function normalizeName(string $name) : string
    {
        return strtr(strtolower($name), '_', '-');
    }
}
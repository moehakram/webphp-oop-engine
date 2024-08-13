<?php
declare(strict_types=1);

namespace MA\PHPQUICK;

class Config extends Collection
{   
    public function get($key, $default = null)
    {
        $config = $this->items;
        $keys = explode('.', $key);

        foreach ($keys as $part) {
            if (isset($config[$part])) {
                $config = $config[$part];
            } else {
                return $default;
            }
        }
        return $config;
    }

    public function set(string $key, $value)
    {
        $keys = explode('.', $key);
        $temp = &$this->items;

        foreach ($keys as $k) {
            if (!isset($temp[$k])) {
                $temp[$k] = [];
            }
            $temp = &$temp[$k];
        }

        $temp = $value;
    }
}
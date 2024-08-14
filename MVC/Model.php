<?php
namespace MA\PHPQUICK\MVC;

use MA\PHPQUICK\Validation\Validator;

abstract class Model extends Validator
{
    final public function __construct(array $data = [])
    {
        parent::__construct($data, $this->rules(), $this->messages());
    }

    protected function rules(): array
    {
        return [];
    }

    protected function messages(): array
    {
       return [];
    }

    final public function has(string $key): bool
    {
        return isset($this->$key);
    }

    final public function get(string $key, $default = null)
    {
        return $this->$key ?? $default;
    }

    final public function set(string $key, $value)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
    }   
}
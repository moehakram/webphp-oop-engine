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

    final public function sanitize(): array {
        $data = [];
        foreach ($this->sanitizationRule as $field => $fieldType) {
            $data[$field] = $this->sanitizeField($field, $fieldType);
        }
        return $data;
    }
    
    private function sanitizeField(string $field, string $fieldType) {
        if (!$this->has($field)) {
            return null;
        }
    
        $filter = $this->getFilter($fieldType);
        $value = $this->get($field);
    
        $result = is_array($filter) ? $this->filterArray($value, $filter) : filter_var($value, $filter);
    
        if ($result !== false) {
            $result = $this->trimValue($result);
            $this->set($field, $result);
            return $result;
        }
    
        return $value;
    }
    
    private function getFilter(string $fieldType) {
        return $this->filterKey()[$fieldType] ?? FILTER_SANITIZE_SPECIAL_CHARS;
    }
    
    private function filterArray($value, array $filter) {
        if (isset($filter['flags'])) {
            return filter_var($value, $filter['filter'], ['flags' => $filter['flags']]);
        }
        return filter_var($value, $filter['filter'], ['options' => $filter['options']]);
    }
    
}
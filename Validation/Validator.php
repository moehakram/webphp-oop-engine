<?php
declare(strict_types = 1);
namespace MA\PHPQUICK\Validation;

use MA\PHPQUICK\Collection;
use MA\PHPQUICK\Exceptions\ValidationException;

class Validator{
    use MethodsValidation;

    const REQUIRED = 'required';
    const EMAIL = 'email';
    const MIN = 'min';
    const MAX = 'max';
    const BETWEEN = 'between';
    const SAME = 'same';
    const SECURE = 'secure';
    const UNIQUE = 'unique';
    const ALPHANUMERIC = 'alphanumeric';
    const ALPHA = 'alpha';
    const NUMERIC = 'numeric';

    protected const DEFAULT_ERROR_MESSAGES = [
        self::REQUIRED => 'Please enter the %s',
        self::EMAIL => 'The %s is not a valid email address',
        self::MIN => 'The %s must have at least %s characters',
        self::MAX => 'The %s must have at most %s characters',
        self::BETWEEN => 'The %s must have between %d and %d characters',
        self::SAME => 'The %s must match with %s',
        self::SECURE => 'The %s must have between 8 and 64 characters and contain at least one number, one upper case letter, one lower case letter and one special character',
        self::UNIQUE => 'The %s already exists',
        self::ALPHANUMERIC => 'The %s should have only letters and numbers',
        self::ALPHA => 'The %s must be a alfhabet value',
        self::NUMERIC => 'The %s must be a numeric value',
    ];

    protected $data = [];
    protected $validationRules = [];
    protected $messages = [];
    protected $errors = [];

    public function __construct(array $data, array $validationRules, array $messages = [])
    {
        $this->loadData($data);
        $this->messages = $messages;
        $this->initializeRules($validationRules);     
    }

    protected function initializeRules(array $fields){
        foreach($fields as $field => $rules){
            $this->validationRules[trim($field)] = is_string($rules) ? $this->split($rules, '|') : $rules;
        }
    }

    protected function split($str, $separator){
        return array_map('trim', explode($separator, $str));
    }

    public function validate() : Collection
    {
        $customRuleMessages = array_filter($this->messages, 'is_string');
        $rulesMessages = array_merge(self::DEFAULT_ERROR_MESSAGES, $customRuleMessages);

        foreach ($this->validationRules as $field => $rules) {
            foreach ($rules as $rule) {
                [$ruleName, $params] = $this->parseRule($rule);
                $methodName = 'is_' . $ruleName;

                if (method_exists($this, $methodName) && !$this->$methodName($field, ...$params)) {
                    $message = $this->messages[$field][$ruleName] ?? $rulesMessages[$ruleName] ?? 'The %s is not valid!';
                    $this->errors[$field] = sprintf($message, $field, ...$params);
                }
            }
        }

        if($this->errors){
            throw new ValidationException('Validation failed', new Collection($this->errors));
        }

        return new Collection($this->data);
    }

    private function parseRule(string $rule): array
    {
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$ruleName, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        } else {
            $ruleName = $rule;
        }

        return [trim($ruleName), $params];
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    private function loadData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    final public function __get(string $name)
    {
        return $this->get($name);
    }
}
<?php

namespace MA\PHPQUICK\Validation;

use MA\PHPQUICK\Collection;
use MA\PHPQUICK\Contracts\ValidationInterface;
use MA\PHPQUICK\Exceptions\ValidationException;

class Validation implements ValidationInterface
{
    use MethodsValidation;

    private const RULE_MESSAGES = [
        'required' => 'Please enter the %s',
        'email' => 'The %s is not a valid email address',
        'min' => 'The %s must have at least %s characters',
        'max' => 'The %s must have at most %s characters',
        'between' => 'The %s must have between %d and %d characters',
        'same' => 'The %s must match with %s',
        'secure' => 'The %s must have between 8 and 64 characters and contain at least one number, one upper case letter, one lower case letter and one special character',
        'unique' => 'The %s already exists',
        'alnum' => 'The %s should have only letters and numbers',
        'alpha' => 'The %s must be an alphabet value',
        'numeric' => 'The %s must be a numeric value',
    ];

    private array $data;
    private array $validationRules;
    private array $messages;

    public function __construct(array $data, array $validationRules, array $messages = [])
    {
        $this->data = $data;
        $this->messages = $messages;
        $this->validationRules = $this->normalizeRules($validationRules);
    }

    private function normalizeRules(array $rules): array
    {
        $normalizedRules = [];
        foreach ($rules as $field => $ruleSet) {
            $normalizedRules[trim($field)] = is_array($ruleSet) ? $ruleSet : $this->split('|' ,$ruleSet);
        }
        return $normalizedRules;
    }

    private function split($separator, $str){
        return array_map('trim', explode($separator, $str));
    }

    public function validate(): Collection
    {
        $allMessages = array_merge(self::RULE_MESSAGES, array_filter($this->messages, 'is_string'));

        $errors = [];
        foreach ($this->validationRules as $field => $rules) {
            foreach ($rules as $rule) {
                [$ruleName, $params] = $this->extractRuleNameAndParams($rule);
                $method = 'is_' . $ruleName;

                if (method_exists($this, $method) && !$this->$method($field, ...$params)) {
                    $message = $this->messages[$field][$ruleName] ?? $allMessages[$ruleName] ?? 'The %s is not valid!';
                    $errors[$field] = sprintf($message, $field, ...$params);
                }
            }
        }

        if ($errors) {
            throw new ValidationException('Validation failed', new Collection($errors));
        }

        return new Collection($this->data);
    }

    private function extractRuleNameAndParams($rule): array
    {
        if (strpos($rule, ':') !== false) {
            [$ruleName, $paramStr] = explode(':', $rule, 2);
            return [trim($ruleName), $this->split(',', $paramStr)];
        }
        return [trim($rule), []];
    }


    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }
}

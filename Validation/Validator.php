<?php
namespace MA\PHPQUICK\Validation;

use MA\PHPQUICK\Collection;
use MA\PHPQUICK\Exceptions\ValidationException;

class Validator{
    use MethodsValidation;

    protected const DEFAULT_ERROR_MESSAGES = [
        'required' => 'Please enter the %s',
        'email' => 'The %s is not a valid email address',
        'min' => 'The %s must have at least %s characters',
        'max' => 'The %s must have at most %s characters',
        'between' => 'The %s must have between %d and %d characters',
        'same' => 'The %s must match with %s',
        'alphanumeric' => 'The %s should have only letters and numbers',
        'secure' => 'The %s must have between 8 and 64 characters and contain at least one number, one upper case letter, one lower case letter and one special character',
        'unique' => 'The %s already exists',
        'numeric' => 'The %s must be a numeric value'
    ];

    protected $data = [];
    protected $sanitizationRule = [];
    protected $validationRules = [];
    protected $messages = [];
    protected $errors = [];

    public function __construct(array $inputs, array $fields, array $messages = [])
    {
        $this->loadData($inputs);
        $this->messages = $messages;
        
        foreach($fields as $field => $rules){
            $field = trim($field);
            $this->validationRules[$field] = is_string($rules) ? $this->split($rules, '|') : $rules;
            foreach($this->validationRules[$field] as $key => &$rule){
                $rule = strtolower($rule);
                if(strpos($rule, '@') !== false){
                    $this->sanitizationRule[$field] = trim(substr($rule, 1));
                    unset($this->validationRules[$field][$key]);
                }
            }
        }
    }

    private function split($str, $separator){
        return array_map('trim', explode($separator, $str));
    }

    public function getSanitizationRule() : array
    {
        return $this->sanitizationRule;
    }

    public function getValidationRules() : array
    {
        return $this->validationRules;
    }

    public function sanitize() : array
    {
        $inputs = [];
        if ($this->sanitizationRule) {
            $options = array_map(fn($field) => $this->filterKey()[$field], $this->sanitizationRule);
            $inputs = filter_var_array($this->data, $options);
        } else {
            $inputs = filter_var_array($this->data, FILTER_SANITIZE_SPECIAL_CHARS);
        }
    
        $this->data = array_merge($this->data, $inputs);
        return $this->trimValue($this->data);
    }

    final protected function trimValue(&$data)
    {
        if (is_array($data)) {
            array_walk($data, [$this, 'trimValue']);
        }
    
        if(is_string($data)){
            $data = trim($data);
        }
        
        return $data;
    }

    /**
     * @return errors
     */
    public function validate() : array
    {
        $ruleMessages = array_filter($this->messages, fn($message) => is_string($message));
        $validationErrors = array_merge(self::DEFAULT_ERROR_MESSAGES, $ruleMessages);

        foreach ($this->validationRules as $field => $rules) {
            foreach ($rules as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $paramStr] = $this->split($rule, ':');
                    $params = $this->split($paramStr, ',');
                } else {
                    $ruleName = trim($rule);
                }
                $methodName = 'is_' . $ruleName;

                if (method_exists($this, $methodName) && !$this->$methodName($field, ...$params)) {
                    $message = $this->messages[$field][$ruleName] ?? $validationErrors[$ruleName] ?? 'The %s is not valid!';
                    $this->errors[$field] = sprintf($message, $field, ...$params);
                }
            }
        }

        if($this->errors){
            throw new ValidationException('errors', new Collection($this->errors));
        }

        return $this->data;
    }

    public function filter() : array 
    {
        $this->sanitize();
        $this->validate();
        return $this->data;
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

    final protected function loadData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    protected function filterKey() : array
    {
        return [
            'string' => FILTER_SANITIZE_SPECIAL_CHARS,
            'string[]' => [
                'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags' => FILTER_REQUIRE_ARRAY
            ],
            'email' => FILTER_SANITIZE_EMAIL,
            'int' => [
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'flags' => FILTER_REQUIRE_SCALAR
            ],
            'int[]' => [
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'flags' => FILTER_REQUIRE_ARRAY
            ],
            'float' => [
                'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                'flags' => FILTER_FLAG_ALLOW_FRACTION
            ],
            'float[]' => [
                'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                'flags' => FILTER_REQUIRE_ARRAY
            ],
            'trim' => [
                'filter' => FILTER_CALLBACK,
                'options' => fn($value) => trim(strip_tags($value)),
            ],
            'url' => FILTER_SANITIZE_URL,
        ];
    }
}
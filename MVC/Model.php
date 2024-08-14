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
}
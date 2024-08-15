<?php
namespace MA\PHPQUICK\MVC;

use MA\PHPQUICK\Validation\Validation;

abstract class Model extends Validation
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
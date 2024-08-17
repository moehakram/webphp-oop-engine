<?php

use MA\PHPQUICK\Collection;
use MA\PHPQUICK\Validation\Validation;
use MA\PHPQUICK\Exceptions\ValidationException;

it('passes validation with valid data', function () {
    $data = [
        'name' => 'Muhammad Akram',
        'email' => 'akram@example.com',
        'password' => 'Password123!',
        'age' => '25'
    ];

    $rules = [
        'name' => 'required|alpha_s',
        'email' => 'required|email',
        'password' => 'required|secure',
        'age' => 'required|numeric'
    ];

    $validator = new Validation($data, $rules);
    $validatedData = $validator->validate();

    expect($validatedData->getAll())->toEqual($data);
});

it('fails validation with invalid email', function () {
    $data = [
        'email' => 'invalid-email'
    ];

    $rules = [
        'email' => 'email|required'
    ];

    $validator = new Validation($data, $rules);

    $this->expectException(ValidationException::class);

    try {
        $validator->validate();
        $this->fail('Validation should failed');
    } catch (ValidationException $e) {
        $errors = $e->getErrors();
        expect($errors)->toBeInstanceOf(Collection::class);
        expect($errors->getAll())->toHaveKey('email');
        expect($errors->get('email'))->toEqual('The email is not a valid email address');
        throw $e;
    }
});

it('fails validation with missing required field', function () {
    $data = [
        'email' => 'akram@example.com'
    ];

    $rules = [
        'name' => 'required',
        'email' => 'required|email',
        'phone' => 'required|numeric'
    ];

    $validator = new Validation($data, $rules);

    $this->expectException(ValidationException::class);

    try {
        $validator->validate();
        $this->fail('Validation should failed');
    } catch (ValidationException $e) {
        $errors = $e->getErrors();
        expect($errors)->toBeInstanceOf(Collection::class);
        expect($errors->getAll())->toHaveKeys(['name', 'phone']);
        expect($errors->get('name'))->toEqual('Please enter the name');
        throw $e;
    }
});

it('fails validation with custom error message', function () {
    $data = [
        'name' => 'Muh. Akram',
        'email' => 'invalid-email'
    ];

    $rules = [
        'name' => 'required|alpha',
        'email' => 'required|email'
    ];

    $messages = [
        'name' => [
            'alpha' => 'Custom error message for invalid alphabet'
        ],
        'email' => [
            'email' => 'Custom error message for invalid email'
        ]
    ];

    $validator = new Validation($data, $rules, $messages);

    $this->expectException(ValidationException::class);

    try {
        $validator->validate();
    } catch (ValidationException $e) {
        $errors = $e->getErrors();
        expect($errors)->toBeInstanceOf(Collection::class);
        expect($errors->getAll())->toHaveKeys(['email', 'name']);
        expect($errors->get('name'))->toEqual('Custom error message for invalid alphabet');
        expect($errors->get('email'))->toEqual('Custom error message for invalid email');
        throw $e;
    }
});

it('has method returns true for existing field', function () {
    $data = [
        'name' => 'Muhammad Akram'
    ];

    $rules = [
        'name' => 'required'
    ];

    $validator = new Validation($data, $rules);

    expect($validator->has('name'))->toBeTrue();
});

it('has method returns false for non-existing field', function () {
    $data = [
        'name' => 'Muhammad Akram'
    ];

    $rules = [
        'name' => 'required'
    ];

    $validator = new Validation($data, $rules);

    expect($validator->has('email'))->toBeFalse();
});

it('get method returns default if field does not exist', function () {
    $data = [
        'name' => 'Muhammad Akram'
    ];

    $rules = [
        'name' => 'required'
    ];

    $validator = new Validation($data, $rules);

    expect($validator->get('email', 'default'))->toEqual('default');
});

it('set method updates field value', function () {
    $data = [
        'name' => 'Muhammad Akram'
    ];

    $rules = [
        'name' => 'required'
    ];

    $validator = new Validation($data, $rules);
    $validator->set('name', 'muh akram');

    expect($validator->get('name'))->toEqual('muh akram');
});

it('passes validation with array rules', function () {
    $data = [
        'email' => 'akram@example.com',
        'password' => 'Password123!',
        'password2' => 'Password123!',
    ];

    $rules = [
        'email' => ['required', 'email'],
        'password' => ['required', 'secure'],
        'password2' => ['required', 'secure', 'same:password'],
    ];

    $validator = new Validation($data, $rules);

    try {
        $result = $validator->validate();
        expect($result->getAll())->toEqual($data);
        expect($result->email)->toEqual('akram@example.com');
    } catch (ValidationException $e) {
        $this->fail('Validation should pass, but it failed with errors: ' . print_r($e->getErrors()->getAll(), true));
    }
});

it('fails validation with array rules', function () {
    $data = [
        'email' => 'invalid-email',
        'password' => 'salah',
    ];

    $rules = [
        'email' => [' required', 'email'],
        'password' => ['required ', 'secure '],
    ];

    $validator = new Validation($data, $rules);

    $this->expectException(ValidationException::class);
    $validator->validate();
});

it('passes validation with valid alphabet', function () {
    $data = [
        'name' => 'Muhammad Akram'
    ];

    $rules = [
        'name' => ['alpha:s']
    ];

    $validator = new Validation($data, $rules);
    try {
        $result = $validator->validate();
        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->getAll())->toEqual($data);
    } catch (ValidationException $e) {
        $this->fail('Validation should pass');
    }
});

it('fails validation with invalid alphabet', function () {
    $data = [
        'name' => '<h1>Muhammad Akram</h1>'
    ];

    $rules = [
        'name' => 'alpha'
    ];

    $validator = new Validation($data, $rules);
    $this->expectException(ValidationException::class);
    try {
        $validator->validate();
        $this->fail('Validation should failed');
    } catch (ValidationException $e) {
        $errors = $e->getErrors();
        expect($errors)->toBeInstanceOf(Collection::class);
        expect($errors->getAll())->toHaveKey('name');
        expect($errors->get('name'))->toEqual('The name must be an alphabet value');
        throw $e;
    }
});

it('validates alpha characters correctly', function () {
    $data = [
        'name1' => 'MuhammadAkram',
        'name2' => 'Muhammad-Akram',
        'name3' => 'Muhammad12Akram',
        'name4' => '809',
        'name5' => '809Muhakda-nask',
        'name6' => '809Muhakda_nask',
        'name7' => 'Muhammad Akram',
    ];

    $rules = [
        'name1' => 'alpha',
        'name2' => 'alpha: -',
        'name3' => 'alpha: n  ',
        'name4' => 'alpha:N  ',
        'name5' => 'alpha:n-',
        'name6' => 'alpha:n _ ',
        'name7' => 'alpha:n_ s',
    ];

    $validator = new Validation($data, $rules);
    try {
        $result = $validator->validate();
        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->getAll())->toEqual($data);
    } catch (ValidationException $e) {
        $this->fail('Validation should pass' . print_r($e->getErrors()->getAll(), true));
    }
});
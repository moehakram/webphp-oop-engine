<?php
declare(strict_types=1);
namespace Tests;

use MA\PHPQUICK\Collection;
use PHPUnit\Framework\TestCase;
use MA\PHPQUICK\Validation\Validation;
use MA\PHPQUICK\Exceptions\ValidationException;

final class ValidationTest extends TestCase
{
    public function testValidationPassesWithValidData(): void
    {
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
        $this->assertEquals($data, $validatedData->getAll());
    }

    public function testValidationFailsWithInvalidEmail(): void
    {
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
            $this->assertInstanceOf(Collection::class, $errors);
            $this->assertArrayHasKey('email', $errors->getAll());
            $this->assertEquals('The email is not a valid email address', $errors->get('email'));
            throw $e;
        }
    }

    public function testValidationFailsWithMissingRequiredField(): void
    {
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
            $this->assertInstanceOf(Collection::class, $errors);
            $this->assertArrayHasKey('name', $errors->getAll());
            $this->assertArrayHasKey('phone', $errors->getAll());
            $this->assertEquals('Please enter the name', $errors->get('name'));
            throw $e;
        }
    }

    public function testValidationFailsWithCustomErrorMessage(): void
    {
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
            $this->assertInstanceOf(Collection::class, $errors);
            $this->assertArrayHasKey('email', $errors->getAll());
            $this->assertArrayHasKey('name', $errors->getAll());
            $this->assertEquals('Custom error message for invalid alphabet', $errors->get('name'));
            $this->assertEquals('Custom error message for invalid email', $errors->get('email'));
            throw $e;
        }
    }

    public function testHasMethodReturnsTrueForExistingField(): void
    {
        $data = [
            'name' => 'Muhammad Akram'
        ];

        $rules = [
            'name' => 'required'
        ];

        $validator = new Validation($data, $rules);

        $this->assertTrue($validator->has('name'));
    }

    public function testHasMethodReturnsFalseForNonExistingField(): void
    {
        $data = [
            'name' => 'Muhammad Akram'
        ];

        $rules = [
            'name' => 'required'
        ];

        $validator = new Validation($data, $rules);

        $this->assertFalse($validator->has('email'));
    }

    public function testGetMethodReturnsDefaultIfFieldDoesNotExist(): void
    {
        $data = [
            'name' => 'Muhammad Akram'
        ];

        $rules = [
            'name' => 'required'
        ];

        $validator = new Validation($data, $rules);

        $this->assertEquals('default', $validator->get('email', 'default'));
    }

    public function testSetMethodUpdatesFieldValue(): void
    {
        $data = [
            'name' => 'Muhammad Akram'
        ];

        $rules = [
            'name' => 'required'
        ];

        $validator = new Validation($data, $rules);
        $validator->set('name', 'muh akram');

        $this->assertEquals('muh akram', $validator->get('name'));
    }

    public function testValidationWithArrayRules(): void
    {
        $data = [
            'email' => 'akram@example.com',
            'password' => 'Password123!',
            'password2' => 'Password123!',
        ];

        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'secure'],
            'password2' => ['required', 'secure', ['same'=>'password']],
        ];

        $validator = new Validation($data, $rules);

        try {
            $result = $validator->validate();
            $this->assertEquals($data, $result->getAll());
            $this->assertEquals('akram@example.com', $result->email);
        } catch (ValidationException $e) {
            $this->fail('Validation should pass, but it failed with errors: ' . print_r($e->getErrors()->getAll(), true));
        }
    }

    public function testValidationFailsWithArrayRules(): void
    {
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
    }

    public function testValidationValidAlfhabet(): void
    {
        $data = [
            'name' => 'Muhammad Akram'
        ];

        $rules = [
            'name' => ['alpha' => 's']
        ];

        $validator = new Validation($data, $rules);
        try {
            $result = $validator->validate();
            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals($data, $result->getAll());
        } catch (ValidationException $e) {
            $this->fail('Validation should pass');
        }
    }


    public function testValidationInValidAlfhabet(): void
    {
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
            $this->assertInstanceOf(Collection::class, $errors);
            $this->assertArrayHasKey('name', $errors->getAll());
            $this->assertEquals('The name must be an alphabet value', $errors->get('name'));
            throw $e;
        }
    }

    public function testIsAlpha()
    {
        $data = [
            'name1' => 'MuhammadAkram',
            'name2' => 'Muhammad-Akram',
            'name3' => 'Muhammad12Akram',
            'name4' => '809',
            'name5' => '809',
            'name5' => '809Muhakda-nask',
            'name6' => '809Muhakda_nask',
            'name7' => 'Muhammad Akram',
        ];

        $rules = [
            'name1' => ['alpha'],
            'name2' => 'alpha: -',
            'name3' => ['alpha'=>' n  '],
            'name4' => 'alpha:N  ',
            'name5' => 'alpha:n-',
            'name6' => 'alpha:n _ ',
            'name7' => ['alpha' => 'n_ s'],
        ];

        $validator = new Validation($data, $rules);
        try {
            $result = $validator->validate();
            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals($data, $result->getAll());
        } catch (ValidationException $e) {
            $this->fail('Validation should pass' . print_r($e->getErrors()->getAll(), true));
        }
    }
}

<?php

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidatesRequiredAndTypedFields(): void
    {
        $validator = new Validator();

        $this->assertTrue($validator->validate([
            'email' => 'user@example.com',
            'quantity' => 2,
            'status' => 'ACTIVE',
        ], [
            'email' => 'required|email',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]));
        $this->assertSame([], $validator->errors());
    }

    public function testCollectsValidationErrors(): void
    {
        $validator = new Validator();

        $this->assertFalse($validator->validate([
            'email' => 'invalid',
            'quantity' => 0,
        ], [
            'email' => 'required|email',
            'quantity' => 'required|integer|min:1',
            'status' => 'required',
        ]));
        $this->assertArrayHasKey('email', $validator->errors());
        $this->assertArrayHasKey('quantity', $validator->errors());
        $this->assertArrayHasKey('status', $validator->errors());
    }
}

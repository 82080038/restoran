<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $fieldRules) as $rule) {
                [$name, $parameter] = array_pad(explode(':', $rule, 2), 2, null);

                if ($name !== 'required' && ($value === null || $value === '')) {
                    continue;
                }

                $this->apply((string) $field, $value, $name, $parameter, $data);
            }
        }

        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function apply(string $field, mixed $value, string $rule, ?string $parameter, array $data): void
    {
        $valid = match ($rule) {
            'required' => $value !== null && $value !== '',
            'string' => is_string($value),
            'array' => is_array($value),
            'boolean' => is_bool($value) || in_array($value, [0, 1, '0', '1'], true),
            'numeric' => is_numeric($value),
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            'date' => strtotime((string) $value) !== false,
            'min' => $this->minimum($value, (int) $parameter),
            'max' => $this->maximum($value, (int) $parameter),
            'in' => in_array((string) $value, explode(',', (string) $parameter), true),
            'same' => $value === ($data[$parameter] ?? null),
            default => false,
        };

        if (!$valid) {
            $this->errors[$field][] = $this->message($field, $rule, $parameter);
        }
    }

    private function minimum(mixed $value, int $minimum): bool
    {
        return is_numeric($value) ? (float) $value >= $minimum : mb_strlen((string) $value) >= $minimum;
    }

    private function maximum(mixed $value, int $maximum): bool
    {
        return is_numeric($value) ? (float) $value <= $maximum : mb_strlen((string) $value) <= $maximum;
    }

    private function message(string $field, string $rule, ?string $parameter): string
    {
        return match ($rule) {
            'required' => "{$field} is required.",
            'string' => "{$field} must be a string.",
            'array' => "{$field} must be an array.",
            'boolean' => "{$field} must be a boolean.",
            'numeric' => "{$field} must be numeric.",
            'integer' => "{$field} must be an integer.",
            'email' => "{$field} must be a valid email address.",
            'url' => "{$field} must be a valid URL.",
            'date' => "{$field} must be a valid date.",
            'min' => "{$field} must be at least {$parameter}.",
            'max' => "{$field} must not exceed {$parameter}.",
            'in' => "{$field} must be one of: {$parameter}.",
            'same' => "{$field} must match {$parameter}.",
            default => "{$field} contains an invalid value.",
        };
    }
}

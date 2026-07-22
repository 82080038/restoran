<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

abstract class BaseController
{
    protected function body(array $request): array
    {
        return is_array($request['body'] ?? null) ? $request['body'] : [];
    }

    protected function tenantId(array $request): int
    {
        $tenantId = $request['tenant_id'] ?? null;

        if (!is_int($tenantId) && !ctype_digit((string) $tenantId)) {
            throw new RuntimeException('Tenant context is required.', 400);
        }

        return (int) $tenantId;
    }

    protected function validate(array $data, array $rules): array
    {
        $validator = new Validator();
        if (!$validator->validate($data, $rules)) {
            throw new ValidationException($validator->errors());
        }

        return $data;
    }
}

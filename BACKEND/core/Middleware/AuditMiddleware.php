<?php

declare(strict_types=1);

namespace App\Core;

final class AuditMiddleware
{
    public static function handle(array $request): array
    {
        $method = strtoupper((string) ($request['method'] ?? 'GET'));
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $request;
        }

        register_shutdown_function(static function () use ($request, $method): void {
            $tenantId = $request['tenant_id'] ?? null;
            if (!is_numeric($tenantId) || (int) $tenantId <= 0) {
                return;
            }

            $payload = self::redact($request['body'] ?? []);
            try {
                Audit::log(
                    (int) $tenantId,
                    isset($request['user_id']) ? (int) $request['user_id'] : null,
                    'API',
                    $method,
                    null,
                    null,
                    null,
                    ['uri' => $request['uri'] ?? null, 'payload' => $payload]
                );
            } catch (\Throwable $exception) {
                error_log('Audit write failed: ' . $exception->getMessage());
            }
        });

        return $request;
    }

    private static function redact(array $payload): array
    {
        foreach (['password', 'password_confirmation', 'token', 'authorization'] as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = '[REDACTED]';
            }
        }

        return $payload;
    }
}

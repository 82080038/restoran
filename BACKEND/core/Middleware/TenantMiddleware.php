<?php

namespace App\Core;

class TenantMiddleware
{

    public static function handle(array $request): array
    {
        $isPlatformOwner = (bool) ($request['is_platform_owner'] ?? false);
        $authenticatedTenantId = $request['tenant_id'] ?? null;
        $requestedTenantId = $request['body']['tenant_id'] ?? $request['query']['tenant_id'] ?? null;

        if (!$isPlatformOwner && ($authenticatedTenantId === null || (int) $authenticatedTenantId <= 0)) {
            Response::error('Tenant context is required.', 400);
        }

        if (!$isPlatformOwner && $requestedTenantId !== null && (int) $requestedTenantId !== (int) $authenticatedTenantId) {
            Response::forbidden('Cross-tenant access is not allowed.');
        }

        if ($isPlatformOwner && $requestedTenantId !== null) {
            $request['tenant_id'] = (int) $requestedTenantId;
        }

        return $request;
    }

    public function validate($tenantId, $isPlatformOwner = false)
    {
        // Platform owners can access without tenant context
        if ($isPlatformOwner) {
            return null; // Return null to indicate platform-level access
        }

        if (empty($tenantId)) {

            Response::error("Tenant ID is required");

        }



        return $tenantId;

    }

}

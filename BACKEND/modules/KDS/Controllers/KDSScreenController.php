<?php

namespace App\Modules\KDS\Controllers;

use App\Core\Response;
use App\Modules\KDS\Services\KDSScreenService;

class KDSScreenController extends BaseController
{
    private $screenService;

    public function __construct()
    {
        $this->screenService = new KDSScreenService();
    }

    public function getScreens($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $stationId = $request['station_id'] ?? null;

            $screens = $this->screenService->getScreens($tenantId, $branchId, $stationId);
            return Response::success($screens, 'KDS screens retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getScreen($request)
    {
        try {
            $request = $this->authMiddleware->handle($request);
            $screenId = $request['id'];
            $tenantId = $request['tenant_id'];

            $screen = $this->screenService->getScreen($screenId, $tenantId);
            if (!$screen) {
                return Response::error('KDS screen not found', 404);
            }
            return Response::success($screen, 'KDS screen retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createScreen($request)
    {
        try {
            $request = $this->authMiddleware->handle($request);
            
            $required = ['tenant_id', 'branch_id', 'station_id', 'screen_name'];
            foreach ($required as $field) {
                if (!isset($request[$field])) {
                    return Response::error("Missing required field: $field", 400);
                }
            }

            $screenId = $this->screenService->createScreen($request);
            return Response::success(['screen_id' => $screenId], 'KDS screen created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateScreen($request)
    {
        try {
            $request = $this->authMiddleware->handle($request);
            $screenId = $request['id'];
            $tenantId = $request['tenant_id'];

            $screen = $this->screenService->getScreen($screenId, $tenantId);
            if (!$screen) {
                return Response::error('KDS screen not found', 404);
            }

            $this->screenService->updateScreen($screenId, $tenantId, $request);
            return Response::success([], 'KDS screen updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deleteScreen($request)
    {
        try {
            $request = $this->authMiddleware->handle($request);
            $screenId = $request['id'];
            $tenantId = $request['tenant_id'];

            $screen = $this->screenService->getScreen($screenId, $tenantId);
            if (!$screen) {
                return Response::error('KDS screen not found', 404);
            }

            $this->screenService->deleteScreen($screenId, $tenantId);
            return Response::success([], 'KDS screen deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}

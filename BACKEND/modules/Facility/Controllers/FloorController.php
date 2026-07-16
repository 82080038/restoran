<?php

namespace App\Modules\Facility\Controllers;

use App\Modules\Facility\Services\FloorService;
use App\Core\AuthMiddleware;
use App\Core\Response;

class FloorController
{
    private $service;
    private $authMiddleware;

    public function __construct()
    {
        $this->service = new FloorService();
        $this->authMiddleware = new AuthMiddleware();
    }

    /**
     * Get all floors
     */
    public function getFloors($request)
    {
        $user = $this->authMiddleware->authenticate();
        
        $tenantId = $user['tenant_id'];
        $branchId = $request['branch_id'] ?? null;
        
        $floors = $this->service->getFloors($tenantId, $branchId);
        
        Response::success($floors, 'Floors retrieved successfully');
    }

    /**
     * Get single floor
     */
    public function getFloor($request)
    {
        $user = $this->authMiddleware->authenticate();
        
        $floorId = $request['floor_id'] ?? null;
        $tenantId = $user['tenant_id'];
        
        if (!$floorId) {
            Response::error('Floor ID is required', 400);
        }
        
        $floor = $this->service->getFloor($floorId, $tenantId);
        
        if (!$floor) {
            Response::error('Floor not found', 404);
        }
        
        Response::success($floor, 'Floor retrieved successfully');
    }

    /**
     * Create new floor
     */
    public function createFloor($request)
    {
        $user = $this->authMiddleware->authenticate();
        
        $data = [
            'tenant_id' => $user['tenant_id'],
            'branch_id' => $request['branch_id'] ?? $user['branch_id'],
            'floor_code' => $request['floor_code'] ?? null,
            'floor_name' => $request['floor_name'] ?? null,
            'floor_level' => $request['floor_level'] ?? 1,
            'floor_type' => $request['floor_type'] ?? 'DINING',
            'description' => $request['description'] ?? null,
            'sort_order' => $request['sort_order'] ?? 0,
            'status' => $request['status'] ?? 'ACTIVE'
        ];
        
        if (!$data['floor_code'] || !$data['floor_name']) {
            Response::error('Floor code and name are required', 400);
        }
        
        $floorId = $this->service->createFloor($data);
        
        Response::success(['floor_id' => $floorId], 'Floor created successfully', 201);
    }

    /**
     * Update floor
     */
    public function updateFloor($request)
    {
        $user = $this->authMiddleware->authenticate();
        
        $floorId = $request['floor_id'] ?? null;
        $tenantId = $user['tenant_id'];
        
        if (!$floorId) {
            Response::error('Floor ID is required', 400);
        }
        
        $data = [
            'floor_code' => $request['floor_code'] ?? null,
            'floor_name' => $request['floor_name'] ?? null,
            'floor_level' => $request['floor_level'] ?? null,
            'floor_type' => $request['floor_type'] ?? null,
            'description' => $request['description'] ?? null,
            'sort_order' => $request['sort_order'] ?? null,
            'status' => $request['status'] ?? null
        ];
        
        $this->service->updateFloor($floorId, $tenantId, $data);
        
        Response::success(null, 'Floor updated successfully');
    }

    /**
     * Delete floor
     */
    public function deleteFloor($request)
    {
        $user = $this->authMiddleware->authenticate();
        
        $floorId = $request['floor_id'] ?? null;
        $tenantId = $user['tenant_id'];
        
        if (!$floorId) {
            Response::error('Floor ID is required', 400);
        }
        
        $this->service->deleteFloor($floorId, $tenantId);
        
        Response::success(null, 'Floor deleted successfully');
    }

    /**
     * Get zones for a floor
     */
    public function getFloorZones($request)
    {
        $user = $this->authMiddleware->authenticate();
        
        $floorId = $request['floor_id'] ?? null;
        $tenantId = $user['tenant_id'];
        
        if (!$floorId) {
            Response::error('Floor ID is required', 400);
        }
        
        $zones = $this->service->getFloorZones($floorId, $tenantId);
        
        Response::success($zones, 'Floor zones retrieved successfully');
    }
}

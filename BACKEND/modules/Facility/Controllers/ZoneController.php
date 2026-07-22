<?php

namespace App\Modules\Facility\Controllers;

use App\Modules\Facility\Services\ZoneService;
use App\Core\Response;

class ZoneController extends BaseController
{
    private $service;
    public function __construct()
    {
        $this->service = new ZoneService();
    }

    /**
     * Get all zones
     */
    public function getZones($request)
    {
        $tenantId = $request['tenant_id'];
        $branchId = $request['branch_id'] ?? null;
        $floorId = $request['floor_id'] ?? null;
        
        $zones = $this->service->getZones($tenantId, $branchId, $floorId);
        
        Response::success($zones, 'Zones retrieved successfully');
    }

    /**
     * Get single zone
     */
    public function getZone($request)
    {
        $zoneId = $request['zone_id'] ?? null;
        $tenantId = $request['tenant_id'];
        
        if (!$zoneId) {
            Response::error('Zone ID is required', 400);
        }
        
        $zone = $this->service->getZone($zoneId, $tenantId);
        
        if (!$zone) {
            Response::error('Zone not found', 404);
        }
        
        Response::success($zone, 'Zone retrieved successfully');
    }

    /**
     * Create new zone
     */
    public function createZone($request)
    {
        $data = [
            'tenant_id' => $request['tenant_id'],
            'branch_id' => $request['branch_id'] ?? $request['branch_id'],
            'floor_id' => $request['floor_id'] ?? null,
            'zone_code' => $request['zone_code'] ?? null,
            'zone_name' => $request['zone_name'] ?? null,
            'zone_type' => $request['zone_type'] ?? 'DINING',
            'service_type' => $request['service_type'] ?? 'TABLE_SERVICE',
            'description' => $request['description'] ?? null,
            'capacity' => $request['capacity'] ?? 0,
            'sort_order' => $request['sort_order'] ?? 0,
            'status' => $request['status'] ?? 'ACTIVE'
        ];
        
        if (!$data['floor_id'] || !$data['zone_code'] || !$data['zone_name']) {
            Response::error('Floor ID, zone code, and name are required', 400);
        }
        
        $zoneId = $this->service->createZone($data);
        
        Response::success(['zone_id' => $zoneId], 'Zone created successfully', 201);
    }

    /**
     * Update zone
     */
    public function updateZone($request)
    {
        $zoneId = $request['zone_id'] ?? null;
        $tenantId = $request['tenant_id'];
        
        if (!$zoneId) {
            Response::error('Zone ID is required', 400);
        }
        
        $data = [
            'zone_code' => $request['zone_code'] ?? null,
            'zone_name' => $request['zone_name'] ?? null,
            'zone_type' => $request['zone_type'] ?? null,
            'service_type' => $request['service_type'] ?? null,
            'description' => $request['description'] ?? null,
            'capacity' => $request['capacity'] ?? null,
            'sort_order' => $request['sort_order'] ?? null,
            'status' => $request['status'] ?? null
        ];
        
        $this->service->updateZone($zoneId, $tenantId, $data);
        
        Response::success(null, 'Zone updated successfully');
    }

    /**
     * Delete zone
     */
    public function deleteZone($request)
    {
        $zoneId = $request['zone_id'] ?? null;
        $tenantId = $request['tenant_id'];
        
        if (!$zoneId) {
            Response::error('Zone ID is required', 400);
        }
        
        $this->service->deleteZone($zoneId, $tenantId);
        
        Response::success(null, 'Zone deleted successfully');
    }

    /**
     * Get tables for a zone
     */
    public function getZoneTables($request)
    {
        $zoneId = $request['zone_id'] ?? null;
        $tenantId = $request['tenant_id'];
        
        if (!$zoneId) {
            Response::error('Zone ID is required', 400);
        }
        
        $tables = $this->service->getZoneTables($zoneId, $tenantId);
        
        Response::success($tables, 'Zone tables retrieved successfully');
    }
}

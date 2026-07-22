<?php

namespace App\Modules\Facility\Controllers;

use App\Modules\Facility\Services\KitchenStationService;
use App\Core\Response;

class KitchenStationController extends BaseController
{
    private $service;
    public function __construct()
    {
        $this->service = new KitchenStationService();
    }

    /**
     * Get all kitchen stations
     */
    public function getKitchenStations($request)
    {
        $tenantId = $request['tenant_id'];
        $branchId = $request['branch_id'] ?? null;
        $floorId = $request['floor_id'] ?? null;
        
        $stations = $this->service->getKitchenStations($tenantId, $branchId, $floorId);
        
        Response::success($stations, 'Kitchen stations retrieved successfully');
    }

    /**
     * Get single kitchen station
     */
    public function getKitchenStation($request)
    {
        $stationId = $request['station_id'] ?? null;
        $tenantId = $request['tenant_id'];
        
        if (!$stationId) {
            Response::error('Station ID is required', 400);
        }
        
        $station = $this->service->getKitchenStation($stationId, $tenantId);
        
        if (!$station) {
            Response::error('Kitchen station not found', 404);
        }
        
        Response::success($station, 'Kitchen station retrieved successfully');
    }

    /**
     * Create new kitchen station
     */
    public function createKitchenStation($request)
    {
        $data = [
            'tenant_id' => $request['tenant_id'],
            'branch_id' => $request['branch_id'] ?? $request['branch_id'],
            'floor_id' => $request['floor_id'] ?? null,
            'station_name' => $request['station_name'] ?? null,
            'station_type' => $request['station_type'] ?? 'PREPARATION',
            'kitchen_code' => $request['kitchen_code'] ?? null,
            'kitchen_category' => $request['kitchen_category'] ?? 'HOT_KITCHEN',
            'description' => $request['description'] ?? null,
            'capacity' => $request['capacity'] ?? 0,
            'is_central' => $request['is_central'] ?? 0,
            'display_order' => $request['display_order'] ?? 0,
            'is_active' => $request['is_active'] ?? 1
        ];
        
        if (!$data['station_name']) {
            Response::error('Station name is required', 400);
        }
        
        $stationId = $this->service->createKitchenStation($data);
        
        Response::success(['station_id' => $stationId], 'Kitchen station created successfully', 201);
    }

    /**
     * Update kitchen station
     */
    public function updateKitchenStation($request)
    {
        $stationId = $request['station_id'] ?? null;
        $tenantId = $request['tenant_id'];
        
        if (!$stationId) {
            Response::error('Station ID is required', 400);
        }
        
        $data = [
            'station_name' => $request['station_name'] ?? null,
            'station_type' => $request['station_type'] ?? null,
            'kitchen_code' => $request['kitchen_code'] ?? null,
            'kitchen_category' => $request['kitchen_category'] ?? null,
            'description' => $request['description'] ?? null,
            'capacity' => $request['capacity'] ?? null,
            'is_central' => $request['is_central'] ?? null,
            'display_order' => $request['display_order'] ?? null,
            'is_active' => $request['is_active'] ?? null
        ];
        
        $this->service->updateKitchenStation($stationId, $tenantId, $data);
        
        Response::success(null, 'Kitchen station updated successfully');
    }

    /**
     * Delete kitchen station
     */
    public function deleteKitchenStation($request)
    {
        $stationId = $request['station_id'] ?? null;
        $tenantId = $request['tenant_id'];
        
        if (!$stationId) {
            Response::error('Station ID is required', 400);
        }
        
        $this->service->deleteKitchenStation($stationId, $tenantId);
        
        Response::success(null, 'Kitchen station deleted successfully');
    }

    /**
     * Get central kitchens
     */
    public function getCentralKitchens($request)
    {
        $tenantId = $request['tenant_id'];
        $branchId = $request['branch_id'] ?? null;
        
        $kitchens = $this->service->getCentralKitchens($tenantId, $branchId);
        
        Response::success($kitchens, 'Central kitchens retrieved successfully');
    }
}

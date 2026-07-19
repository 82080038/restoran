<?php

namespace App\Modules\BeachClubAdvanced\Controllers;

use App\Core\Response;
use App\Modules\BeachClubAdvanced\Services\BeachClubAdvancedService;

class BeachClubAdvancedController
{
    private $service;

    public function __construct()
    {
        $this->service = new BeachClubAdvancedService();
    }

    public function getSeatMap($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $result = $this->service->getSeatMap($request['tenant_id'], $request['branch_id'] ?? null);
            return Response::success($result, 'Seat map retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getSeatAvailability($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $date = $request['query']['date'] ?? date('Y-m-d');
            $result = $this->service->getSeatAvailability($request['tenant_id'], $request['branch_id'] ?? null, $date);
            return Response::success($result, 'Seat availability retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addSeat($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['zone_name']) || empty($data['seat_label']) || empty($data['seat_type'])) {
                return Response::error('zone_name, seat_label, and seat_type are required', 400);
            }
            return Response::success($this->service->addSeat($data), 'Seat added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function updateSeatPosition($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->updateSeatPosition($id, $request['body']['position_x'] ?? 0, $request['body']['position_y'] ?? 0), 'Seat position updated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getRainChecks($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $result = $this->service->getRainChecks($request['tenant_id'], $request['branch_id'] ?? null, $request['query']['status'] ?? null);
            return Response::success($result, 'Rain checks retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createRainCheck($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            $data['issued_by'] = $request['user_id'] ?? null;
            if (empty($data['customer_name']) || empty($data['original_date'])) {
                return Response::error('customer_name and original_date are required', 400);
            }
            return Response::success($this->service->createRainCheck($data), 'Rain check created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function rescheduleRainCheck($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->rescheduleRainCheck($id, $request['body']['new_date'], $request['body']['rescheduled_to'] ?? null), 'Rain check rescheduled');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function refundRainCheck($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->refundRainCheck($id), 'Rain check refunded');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getWeatherPolicies($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $result = $this->service->getWeatherPolicies($request['tenant_id'], $request['branch_id'] ?? null);
            return Response::success($result, 'Weather policies retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createWeatherPolicy($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['policy_name'])) {
                return Response::error('policy_name is required', 400);
            }
            return Response::success($this->service->createWeatherPolicy($data), 'Weather policy created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }
}

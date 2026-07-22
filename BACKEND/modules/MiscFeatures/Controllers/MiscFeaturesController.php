<?php

namespace App\Modules\MiscFeatures\Controllers;

use App\Core\Response;
use App\Modules\MiscFeatures\Services\MiscFeaturesService;

class MiscFeaturesController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new MiscFeaturesService();
    }

    // ==================== COAT CHECK ====================

    public function checkInCoat($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            $data['handled_by'] = $request['user_id'] ?? null;
            return Response::success($this->service->checkInCoat($data), 'Coat checked in');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function checkOutCoat($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->checkOutCoat($id, $request['user_id'] ?? null), 'Coat checked out');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getCoatCheckItems($request)
    {
        try {
            $result = $this->service->getCoatCheckItems($request['tenant_id'], $request['branch_id'] ?? null, $request['query']['status'] ?? null);
            return Response::success($result, 'Coat check items retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getCoatCheckStats($request)
    {
        try {
            $eventId = $request['query']['event_id'] ?? null;
            $result = $this->service->getCoatCheckStats($request['tenant_id'], $request['branch_id'] ?? null, $eventId);
            return Response::success($result, 'Coat check stats retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== KARAOKE SCORE ====================

    public function recordScore($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['room_id'])) return Response::error('room_id is required', 400);
            return Response::success($this->service->recordScore($data), 'Score recorded');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getHighScores($request)
    {
        try {
            $limit = (int)($request['query']['limit'] ?? 20);
            $result = $this->service->getHighScores($request['tenant_id'], $request['branch_id'] ?? null, $limit);
            return Response::success($result, 'High scores retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== EQUIPMENT TRACKING ====================

    public function getEquipment($request)
    {
        try {
            $result = $this->service->getEquipment($request['tenant_id'], $request['branch_id'] ?? null, $request['query']['status'] ?? null, $request['query']['type'] ?? null);
            return Response::success($result, 'Equipment retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addEquipment($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['equipment_name'])) return Response::error('equipment_name is required', 400);
            return Response::success($this->service->addEquipment($data), 'Equipment added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function assignEquipment($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $data = $request['body'];
            return Response::success($this->service->assignEquipment($id, $data['event_id'] ?? null, $data['room_id'] ?? null, $request['user_id'] ?? null), 'Equipment assigned');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function returnEquipment($request)
    {
        try {
            $id = $request['params']['assignment_id'] ?? $request['id'] ?? null;
            $condition = $request['body']['condition_at_return'] ?? null;
            return Response::success($this->service->returnEquipment($id, $condition), 'Equipment returned');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== RADIUS CLAUSE ====================

    public function checkRadiusClause($request)
    {
        try {
            $data = $request['body'];
            if (empty($data['deal_id']) || empty($data['artist_name']) || empty($data['event_date'])) {
                return Response::error('deal_id, artist_name, and event_date are required', 400);
            }
            $result = $this->service->checkRadiusClause(
                $request['tenant_id'], $data['deal_id'], $data['artist_name'],
                $data['radius_km'] ?? 50, $data['days'] ?? 30, $data['event_date']
            );
            return Response::success($result, 'Radius clause checked');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== SOCIAL GROUP BOOKING ====================

    public function createGroupBooking($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['organizer_name']) || empty($data['event_date'])) {
                return Response::error('organizer_name and event_date are required', 400);
            }
            return Response::success($this->service->createGroupBooking($data), 'Group booking created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addGroupMember($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $data = $request['body'];
            if (empty($data['member_name'])) return Response::error('member_name is required', 400);
            return Response::success($this->service->addGroupMember($id, $data), 'Member added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function payShare($request)
    {
        try {
            $id = $request['params']['member_id'] ?? $request['id'] ?? null;
            return Response::success($this->service->payShare($id), 'Share paid');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getGroupBooking($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $result = $this->service->getGroupBooking($id);
            if (!$result['booking']) return Response::notFound('Group booking not found');
            return Response::success($result, 'Group booking retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== WINE PAIRING ====================

    public function getWines($request)
    {
        try {
            $result = $this->service->getWines($request['tenant_id'], $request['query']['type'] ?? null);
            return Response::success($result, 'Wines retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addWine($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            if (empty($data['wine_name'])) return Response::error('wine_name is required', 400);
            return Response::success($this->service->addWine($data), 'Wine added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addPairingSuggestion($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            if (empty($data['wine_id']) || empty($data['product_id'])) {
                return Response::error('wine_id and product_id are required', 400);
            }
            return Response::success($this->service->addPairingSuggestion($data), 'Pairing suggestion added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getPairingsForProduct($request)
    {
        try {
            $productId = $request['params']['product_id'] ?? $request['query']['product_id'] ?? null;
            $result = $this->service->getPairingsForProduct($request['tenant_id'], $productId);
            return Response::success($result, 'Wine pairings retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== WAITER BUTTON ====================

    public function recordPress($request)
    {
        try {
            $data = $request['body'];
            if (empty($data['room_id'])) return Response::error('room_id is required', 400);
            return Response::success($this->service->recordPress($request['tenant_id'], $request['branch_id'] ?? null, $data['room_id']), 'Press recorded');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function respondToPress($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $type = $request['body']['response_type'] ?? 'ACKNOWLEDGED';
            return Response::success($this->service->respondToPress($id, $request['user_id'] ?? null, $type), 'Press responded');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getWaiterButtonStats($request)
    {
        try {
            $result = $this->service->getWaiterButtonStats(
                $request['tenant_id'], $request['branch_id'] ?? null,
                $request['query']['date_from'] ?? date('Y-m-d'),
                $request['query']['date_to'] ?? date('Y-m-d')
            );
            return Response::success($result, 'Waiter button stats retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== ENTERTAINER ROTATION ====================

    public function addRotationSlot($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['entertainer_name'])) return Response::error('entertainer_name is required', 400);
            return Response::success($this->service->addRotationSlot($data), 'Rotation slot added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getRotationSchedule($request)
    {
        try {
            $eventId = $request['params']['event_id'] ?? $request['query']['event_id'] ?? null;
            $result = $this->service->getRotationSchedule($request['tenant_id'], $eventId);
            return Response::success($result, 'Rotation schedule retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function updateRotationStatus($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $status = $request['body']['status'] ?? null;
            if (!$status) return Response::error('status is required', 400);
            return Response::success($this->service->updateRotationStatus($id, $status), 'Rotation status updated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }
}

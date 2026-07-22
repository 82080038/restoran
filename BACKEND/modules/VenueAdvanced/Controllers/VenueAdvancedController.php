<?php

namespace App\Modules\VenueAdvanced\Controllers;

use App\Core\Response;
use App\Modules\VenueAdvanced\Services\VenueAdvancedService;

class VenueAdvancedController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new VenueAdvancedService();
    }

    // ==================== DYNAMIC PRICING ====================

    public function getPricingRules($request)
    {
        try {
            $result = $this->service->getPricingRules($request['tenant_id'], $request['branch_id'] ?? null);
            return Response::success($result, 'Pricing rules retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createPricingRule($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['rule_name']) || empty($data['trigger_type']) || empty($data['price_modifier_type'])) {
                return Response::error('rule_name, trigger_type, and price_modifier_type are required', 400);
            }
            return Response::success($this->service->createPricingRule($data), 'Pricing rule created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function calculatePrice($request)
    {
        try {
            $data = $request['body'];
            $result = $this->service->calculateDynamicPrice(
                $request['tenant_id'], $request['branch_id'] ?? null,
                $data['product_id'], $data['base_price'] ?? 0, $data['context'] ?? []
            );
            return Response::success(['adjusted_price' => $result], 'Dynamic price calculated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== MEMBERSHIP ====================

    public function getMemberships($request)
    {
        try {
            $result = $this->service->getMemberships($request['tenant_id'], $request['branch_id'] ?? null, $request['query']['tier'] ?? null, $request['query']['status'] ?? null);
            return Response::success($result, 'Memberships retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createMembership($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['member_name'])) return Response::error('member_name is required', 400);
            return Response::success($this->service->createMembership($data), 'Membership created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function earnPoints($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $data = $request['body'];
            return Response::success($this->service->earnPoints($id, $data['points'] ?? 0, $data['order_id'] ?? null, $data['description'] ?? null), 'Points earned');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function redeemPoints($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $data = $request['body'];
            $result = $this->service->redeemPoints($id, $data['points'] ?? 0, $data['description'] ?? null);
            if (!$result['success']) return Response::error($result['message'], 400);
            return Response::success($result, 'Points redeemed');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== QR TICKET SCANNING ====================

    public function scanTicket($request)
    {
        try {
            $data = $request['body'];
            if (empty($data['qr_code'])) return Response::error('qr_code is required', 400);
            $result = $this->service->scanTicket(
                $request['tenant_id'], $request['branch_id'] ?? null,
                $data['event_id'] ?? null, $data['qr_code'],
                $request['user_id'] ?? null, $data['device_id'] ?? null
            );
            return Response::success($result, 'Ticket scanned');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getScanStats($request)
    {
        try {
            $eventId = $request['params']['event_id'] ?? $request['query']['event_id'] ?? null;
            $result = $this->service->getScanStats($request['tenant_id'], $eventId);
            return Response::success($result, 'Scan stats retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== OCCUPANCY ====================

    public function getOccupancy($request)
    {
        try {
            $result = $this->service->getOccupancy($request['tenant_id'], $request['branch_id'] ?? null);
            return Response::success($result ?: [], 'Occupancy retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function recordEntry($request)
    {
        try {
            $count = (int)($request['body']['count'] ?? 1);
            $result = $this->service->recordEntry($request['tenant_id'], $request['branch_id'] ?? null, $count);
            return Response::success($result, 'Entry recorded');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function recordExit($request)
    {
        try {
            $count = (int)($request['body']['count'] ?? 1);
            $result = $this->service->recordExit($request['tenant_id'], $request['branch_id'] ?? null, $count);
            return Response::success($result, 'Exit recorded');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function setMaxCapacity($request)
    {
        try {
            $capacity = (int)($request['body']['max_capacity'] ?? 100);
            return Response::success($this->service->setMaxCapacity($request['tenant_id'], $request['branch_id'] ?? null, $capacity), 'Max capacity set');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== KARAOKE ROOM CALENDAR ====================

    public function getRoomCalendar($request)
    {
        try {
            $roomId = $request['params']['room_id'] ?? $request['query']['room_id'] ?? null;
            $dateFrom = $request['query']['date_from'] ?? date('Y-m-d');
            $dateTo = $request['query']['date_to'] ?? date('Y-m-d', strtotime('+7 days'));
            $result = $this->service->getRoomCalendar($request['tenant_id'], $request['branch_id'] ?? null, $roomId, $dateFrom, $dateTo);
            return Response::success($result, 'Room calendar retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addCalendarBlock($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['room_id']) || empty($data['start_time']) || empty($data['end_time'])) {
                return Response::error('room_id, start_time, and end_time are required', 400);
            }
            return Response::success($this->service->addCalendarBlock($data), 'Calendar block added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== KARAOKE OVERTIME ====================

    public function calculateOvertime($request)
    {
        try {
            $data = $request['body'];
            if (empty($data['room_id']) || empty($data['booked_end_time']) || empty($data['actual_end_time'])) {
                return Response::error('room_id, booked_end_time, and actual_end_time are required', 400);
            }
            $result = $this->service->calculateOvertime(
                $request['tenant_id'], $request['branch_id'] ?? null,
                $data['room_id'], $data['reservation_id'] ?? null,
                $data['booked_end_time'], $data['actual_end_time'],
                $data['rate_per_hour'] ?? 0
            );
            return Response::success($result, 'Overtime calculated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function waiveOvertime($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->waiveOvertime($id), 'Overtime waived');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== HOLDS CALENDAR ====================

    public function getHolds($request)
    {
        try {
            $result = $this->service->getHolds($request['tenant_id'], $request['branch_id'] ?? null, $request['query']['date'] ?? null);
            return Response::success($result, 'Holds retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addHold($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['event_date'])) return Response::error('event_date is required', 400);
            return Response::success($this->service->addHold($data), 'Hold added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function releaseHold($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $rolledTo = $request['body']['rolled_to_date'] ?? null;
            return Response::success($this->service->releaseHold($id, $rolledTo), 'Hold released');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function confirmHold($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->confirmHold($id), 'Hold confirmed');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== COMP / GUEST LIST ====================

    public function getCompList($request)
    {
        try {
            $eventId = $request['params']['event_id'] ?? $request['query']['event_id'] ?? null;
            $listType = $request['query']['list_type'] ?? null;
            $result = $this->service->getCompList($request['tenant_id'], $eventId, $listType);
            return Response::success($result, 'Comp list retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addCompGuest($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            $data['added_by'] = $request['user_id'] ?? null;
            if (empty($data['event_id']) || empty($data['guest_name'])) {
                return Response::error('event_id and guest_name are required', 400);
            }
            return Response::success($this->service->addCompGuest($data), 'Comp guest added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function checkInCompGuest($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->checkInCompGuest($id), 'Comp guest checked in');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }
}

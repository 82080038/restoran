<?php

namespace App\Modules\Nightclub\Controllers;

use App\Core\Response;
use App\Modules\Nightclub\Services\NightclubService;

class NightclubController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new NightclubService();
    }

    // ==================== EVENTS ====================

    public function getEvents($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $status = $request['query']['status'] ?? null;

            $events = $this->service->getEvents($tenantId, $branchId, $status);
            return Response::success($events, 'Events retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getEvent($request)
    {
        try {
            $eventId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $event = $this->service->getEvent($eventId, $tenantId);
            if (!$event) {
                return Response::notFound('Event not found');
            }
            return Response::success($event, 'Event retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createEvent($request)
    {
        try {
            $request['tenant_id'] = $request['tenant_id'];
            $request['created_by'] = $request['user_id'] ?? null;

            if (empty($request['body']['event_name']) || empty($request['body']['event_date'])) {
                return Response::error('event_name and event_date are required', 400);
            }

            $data = array_merge($request['body'], ['tenant_id' => $request['tenant_id'], 'created_by' => $request['created_by']]);
            $eventId = $this->service->createEvent($data);
            return Response::success(['event_id' => $eventId], 'Event created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateEvent($request)
    {
        try {
            $eventId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            if (!$this->service->getEvent($eventId, $tenantId)) {
                return Response::notFound('Event not found');
            }

            $this->service->updateEvent($eventId, $tenantId, $request['body'] ?? []);
            return Response::success([], 'Event updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deleteEvent($request)
    {
        try {
            $eventId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            if (!$this->service->getEvent($eventId, $tenantId)) {
                return Response::notFound('Event not found');
            }

            $this->service->deleteEvent($eventId, $tenantId);
            return Response::success([], 'Event deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    // ==================== ENTRANCE FEES ====================

    public function getEntranceFees($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $eventId = $request['query']['event_id'] ?? null;

            $fees = $this->service->getEntranceFees($tenantId, $eventId);
            return Response::success($fees, 'Entrance fees retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createEntranceFee($request)
    {
        try {
            if (empty($request['body']['fee_name']) || !isset($request['body']['price'])) {
                return Response::error('fee_name and price are required', 400);
            }

            $data = array_merge($request['body'], ['tenant_id' => $request['tenant_id']]);
            $feeId = $this->service->createEntranceFee($data);
            return Response::success(['fee_id' => $feeId], 'Entrance fee created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateEntranceFee($request)
    {
        try {
            $feeId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $this->service->updateEntranceFee($feeId, $tenantId, $request['body'] ?? []);
            return Response::success([], 'Entrance fee updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deleteEntranceFee($request)
    {
        try {
            $feeId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $this->service->deleteEntranceFee($feeId, $tenantId);
            return Response::success([], 'Entrance fee deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    // ==================== ENTRANCE TICKETS ====================

    public function getEntranceTickets($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $eventId = $request['query']['event_id'] ?? null;
            $checkIn = $request['query']['check_in_status'] ?? null;

            $tickets = $this->service->getEntranceTickets($tenantId, $eventId, $checkIn !== null ? (int)$checkIn : null);
            return Response::success($tickets, 'Entrance tickets retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function sellEntranceTicket($request)
    {
        try {
            if (empty($request['body']['customer_name']) || !isset($request['body']['unit_price'])) {
                return Response::error('customer_name and unit_price are required', 400);
            }

            $data = array_merge($request['body'], ['tenant_id' => $request['tenant_id'], 'sold_by' => $request['user_id'] ?? null]);
            $result = $this->service->createEntranceTicket($data);
            return Response::success($result, 'Ticket sold successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function checkInTicket($request)
    {
        try {
            $ticketId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $success = $this->service->checkInTicket($ticketId, $tenantId);
            if (!$success) {
                return Response::error('Ticket already checked in or not found', 400);
            }
            return Response::success([], 'Check-in successful');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    // ==================== GUEST LIST ====================

    public function getGuestList($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $eventId = $request['query']['event_id'] ?? null;
            $checkIn = $request['query']['check_in_status'] ?? null;

            $guests = $this->service->getGuestList($tenantId, $eventId, $checkIn !== null ? (int)$checkIn : null);
            return Response::success($guests, 'Guest list retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function addGuestListEntry($request)
    {
        try {
            if (empty($request['body']['guest_name'])) {
                return Response::error('guest_name is required', 400);
            }

            $data = array_merge($request['body'], ['tenant_id' => $request['tenant_id']]);
            $id = $this->service->addGuestListEntry($data);
            return Response::success(['guest_list_id' => $id], 'Guest added to list successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateGuestListEntry($request)
    {
        try {
            $guestListId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $this->service->updateGuestListEntry($guestListId, $tenantId, $request['body'] ?? []);
            return Response::success([], 'Guest list entry updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function checkInGuest($request)
    {
        try {
            $guestListId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $success = $this->service->checkInGuest($guestListId, $tenantId);
            if (!$success) {
                return Response::error('Guest already checked in or not found', 400);
            }
            return Response::success([], 'Guest check-in successful');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deleteGuestListEntry($request)
    {
        try {
            $guestListId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $this->service->deleteGuestListEntry($guestListId, $tenantId);
            return Response::success([], 'Guest list entry deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    // ==================== BOTTLE SERVICE ====================

    public function getBottleServiceReservations($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $eventId = $request['query']['event_id'] ?? null;
            $status = $request['query']['status'] ?? null;

            $reservations = $this->service->getBottleServiceReservations($tenantId, $eventId, $status);
            return Response::success($reservations, 'Bottle service reservations retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createBottleServiceReservation($request)
    {
        try {
            if (empty($request['body']['customer_name']) || empty($request['body']['package_name']) || empty($request['body']['reservation_date'])) {
                return Response::error('customer_name, package_name, and reservation_date are required', 400);
            }

            $data = array_merge($request['body'], ['tenant_id' => $request['tenant_id']]);
            $id = $this->service->createBottleServiceReservation($data);
            return Response::success(['bottle_service_id' => $id], 'Bottle service reservation created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateBottleServiceReservation($request)
    {
        try {
            $reservationId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $this->service->updateBottleServiceReservation($reservationId, $tenantId, $request['body'] ?? []);
            return Response::success([], 'Bottle service reservation updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deleteBottleServiceReservation($request)
    {
        try {
            $reservationId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $this->service->deleteBottleServiceReservation($reservationId, $tenantId);
            return Response::success([], 'Bottle service reservation deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    // ==================== TABLE RESERVATIONS ====================

    public function getTableReservations($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $eventId = $request['query']['event_id'] ?? null;
            $status = $request['query']['status'] ?? null;

            $reservations = $this->service->getTableReservations($tenantId, $eventId, $status);
            return Response::success($reservations, 'Table reservations retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createTableReservation($request)
    {
        try {
            if (empty($request['body']['customer_name']) || empty($request['body']['party_size']) || empty($request['body']['reservation_date'])) {
                return Response::error('customer_name, party_size, and reservation_date are required', 400);
            }

            $data = array_merge($request['body'], ['tenant_id' => $request['tenant_id'], 'assigned_by' => $request['user_id'] ?? null]);
            $id = $this->service->createTableReservation($data);
            return Response::success(['reservation_id' => $id], 'Table reservation created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateTableReservation($request)
    {
        try {
            $reservationId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $this->service->updateTableReservation($reservationId, $tenantId, $request['body'] ?? []);
            return Response::success([], 'Table reservation updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deleteTableReservation($request)
    {
        try {
            $reservationId = $request['params']['id'] ?? $request['id'] ?? null;
            $tenantId = $request['tenant_id'];

            $this->service->deleteTableReservation($reservationId, $tenantId);
            return Response::success([], 'Table reservation deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    // ==================== DASHBOARD ====================

    public function getDashboardStats($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $eventId = $request['query']['event_id'] ?? null;

            $stats = $this->service->getDashboardStats($tenantId, $eventId);
            return Response::success($stats, 'Nightclub dashboard stats retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    // ==================== REVENUE REPORT ====================

    public function getRevenueReport($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $startDate = $request['query']['start_date'] ?? null;
            $endDate = $request['query']['end_date'] ?? null;

            $report = $this->service->getRevenueReport($tenantId, $startDate, $endDate);
            return Response::success($report, 'Nightclub revenue report retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}

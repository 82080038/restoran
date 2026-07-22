<?php

namespace App\Modules\Entertainment\Controllers;

use App\Core\Response;
use App\Modules\Entertainment\Services\EntertainmentService;

/**
 * EntertainmentController - Handles Karaoke Bar, Beach Club, and Live Music Venue API
 */
class EntertainmentController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new EntertainmentService();
    }

    // ==================== KARAOKE BAR ====================

    public function getKaraokeRooms($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $roomType = $request['query']['room_type'] ?? null;
            $data = $this->service->getKaraokeRooms($tenantId, $roomType);
            return Response::success($data, 'Karaoke rooms retrieved');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createKaraokeRoom($request)
    {
        try {
            $request['body']['tenant_id'] = $request['tenant_id'];
            $id = $this->service->createKaraokeRoom($request['body']);
            return Response::success(['room_id' => $id], 'Karaoke room created');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getKaraokeReservations($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $date = $request['query']['date'] ?? null;
            $status = $request['query']['status'] ?? null;
            $data = $this->service->getKaraokeReservations($tenantId, $date, $status);
            return Response::success($data, 'Karaoke reservations retrieved');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createKaraokeReservation($request)
    {
        try {
            $request['body']['tenant_id'] = $request['tenant_id'];
            $id = $this->service->createKaraokeReservation($request['body']);
            return Response::success(['reservation_id' => $id], 'Karaoke reservation created');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function checkInKaraoke($request)
    {
        try {
            $reservationId = $request['params']['id'] ?? null;
            $result = $this->service->checkInKaraoke($reservationId, $request['tenant_id']);
            return Response::success(['checked_in' => $result], $result ? 'Checked in' : 'Check-in failed');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function checkOutKaraoke($request)
    {
        try {
            $reservationId = $request['params']['id'] ?? null;
            $actualEndTime = $request['body']['actual_end_time'] ?? date('H:i:s');
            $totalBill = $request['body']['total_bill'] ?? 0;
            $result = $this->service->checkOutKaraoke($reservationId, $request['tenant_id'], $actualEndTime, $totalBill);
            return Response::success(['checked_out' => $result], $result ? 'Checked out' : 'Check-out failed');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    // ==================== BEACH CLUB ====================

    public function getBeachCabanas($request)
    {
        try {
            $cabanaType = $request['query']['cabana_type'] ?? null;
            $data = $this->service->getBeachCabanas($request['tenant_id'], $cabanaType);
            return Response::success($data, 'Beach cabanas retrieved');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createBeachCabana($request)
    {
        try {
            $request['body']['tenant_id'] = $request['tenant_id'];
            $id = $this->service->createBeachCabana($request['body']);
            return Response::success(['cabana_id' => $id], 'Beach cabana created');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getBeachReservations($request)
    {
        try {
            $date = $request['query']['date'] ?? null;
            $status = $request['query']['status'] ?? null;
            $data = $this->service->getBeachReservations($request['tenant_id'], $date, $status);
            return Response::success($data, 'Beach club reservations retrieved');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createBeachReservation($request)
    {
        try {
            $request['body']['tenant_id'] = $request['tenant_id'];
            $id = $this->service->createBeachReservation($request['body']);
            return Response::success(['reservation_id' => $id], 'Beach club reservation created');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getBeachEvents($request)
    {
        try {
            $status = $request['query']['status'] ?? null;
            $data = $this->service->getBeachEvents($request['tenant_id'], $status);
            return Response::success($data, 'Beach club events retrieved');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createBeachEvent($request)
    {
        try {
            $request['body']['tenant_id'] = $request['tenant_id'];
            $id = $this->service->createBeachEvent($request['body']);
            return Response::success(['event_id' => $id], 'Beach club event created');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    // ==================== LIVE MUSIC VENUE ====================

    public function getConcerts($request)
    {
        try {
            $status = $request['query']['status'] ?? null;
            $data = $this->service->getConcerts($request['tenant_id'], $status);
            return Response::success($data, 'Concerts retrieved');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createConcert($request)
    {
        try {
            $request['body']['tenant_id'] = $request['tenant_id'];
            $id = $this->service->createConcert($request['body']);
            return Response::success(['concert_id' => $id], 'Concert created');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getSeatingSections($request)
    {
        try {
            $data = $this->service->getSeatingSections($request['tenant_id']);
            return Response::success($data, 'Seating sections retrieved');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createSeatingSection($request)
    {
        try {
            $request['body']['tenant_id'] = $request['tenant_id'];
            $id = $this->service->createSeatingSection($request['body']);
            return Response::success(['section_id' => $id], 'Seating section created');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getConcertTickets($request)
    {
        try {
            $concertId = $request['query']['concert_id'] ?? null;
            $data = $this->service->getConcertTickets($request['tenant_id'], $concertId);
            return Response::success($data, 'Concert tickets retrieved');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createConcertTicket($request)
    {
        try {
            $request['body']['tenant_id'] = $request['tenant_id'];
            $result = $this->service->createConcertTicket($request['body']);
            return Response::success($result, 'Concert ticket created');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function checkInConcertTicket($request)
    {
        try {
            $ticketId = $request['params']['id'] ?? null;
            $result = $this->service->checkInConcertTicket($ticketId, $request['tenant_id']);
            return Response::success(['checked_in' => $result], $result ? 'Checked in' : 'Check-in failed');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    // ==================== DASHBOARD & REPORTS ====================

    public function getDashboardStats($request)
    {
        try {
            $businessType = $request['query']['business_type'] ?? 'KARAOKE_BAR';
            $stats = $this->service->getDashboardStats($request['tenant_id'], $businessType);
            return Response::success($stats, 'Dashboard stats retrieved');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getRevenueReport($request)
    {
        try {
            $businessType = $request['query']['business_type'] ?? 'KARAOKE_BAR';
            $startDate = $request['query']['start_date'] ?? null;
            $endDate = $request['query']['end_date'] ?? null;
            $report = $this->service->getRevenueReport($request['tenant_id'], $businessType, $startDate, $endDate);
            return Response::success($report, 'Revenue report retrieved');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}

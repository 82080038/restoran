<?php

namespace App\Modules\KDS\Controllers;

use App\Core\Response;
use App\Modules\KDS\Services\KDSTicketService;

class KDSTicketController
{
    private $ticketService;

    public function __construct()
    {
        $this->ticketService = new KDSTicketService();
    }

    public function getTickets($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $stationId = $request['station_id'] ?? null;
            $status = $request['status'] ?? null;

            $tickets = $this->ticketService->getTickets($tenantId, $branchId, $stationId, $status);
            return Response::success($tickets, 'KDS tickets retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getTicket($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $ticketId = $request['id'];
            $tenantId = $request['tenant_id'];

            $ticket = $this->ticketService->getTicket($ticketId, $tenantId);
            if (!$ticket) {
                return Response::error('KDS ticket not found', 404);
            }
            return Response::success($ticket, 'KDS ticket retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createTicket($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            
            $required = ['tenant_id', 'branch_id', 'order_id', 'station_id', 'screen_id', 'ticket_number'];
            foreach ($required as $field) {
                if (!isset($request[$field])) {
                    return Response::error("Missing required field: $field", 400);
                }
            }

            $ticketId = $this->ticketService->createTicket($request);
            return Response::success(['ticket_id' => $ticketId], 'KDS ticket created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateTicketStatus($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $ticketId = $request['id'];
            $tenantId = $request['tenant_id'];
            $status = $request['status'];
            $urgencyLevel = $request['urgency_level'] ?? null;

            $ticket = $this->ticketService->getTicket($ticketId, $tenantId);
            if (!$ticket) {
                return Response::error('KDS ticket not found', 404);
            }

            $this->ticketService->updateTicketStatus($ticketId, $tenantId, $status, $urgencyLevel);
            return Response::success([], 'KDS ticket status updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateUrgencyLevels($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            
            $this->ticketService->updateUrgencyLevels();
            return Response::success([], 'KDS ticket urgency levels updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deleteTicket($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $ticketId = $request['id'];
            $tenantId = $request['tenant_id'];

            $ticket = $this->ticketService->getTicket($ticketId, $tenantId);
            if (!$ticket) {
                return Response::error('KDS ticket not found', 404);
            }

            $this->ticketService->deleteTicket($ticketId, $tenantId);
            return Response::success([], 'KDS ticket deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}

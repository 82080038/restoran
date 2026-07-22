<?php

namespace App\Modules\Operations\Controllers;

use App\Core\Response;
use App\Modules\Operations\Services\AYCEService;

class AYCEController extends BaseController
{
    private $ayceService;

    public function __construct()
    {
        $this->ayceService = new AYCEService();
    }

    public function getAYCESessions($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $status = $request['status'] ?? null;

            $sessions = $this->ayceService->getAYCESessions($tenantId, $branchId, $status);
            return Response::success($sessions, 'AYCE sessions retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getAYCESession($request)
    {
        try {
            $sessionId = $request['id'];
            $tenantId = $request['tenant_id'];

            $session = $this->ayceService->getAYCESession($sessionId, $tenantId);
            if (!$session) {
                return Response::error('AYCE session not found', 404);
            }
            return Response::success($session, 'AYCE session retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createAYCESession($request)
    {
        try {
            $required = ['tenant_id', 'branch_id', 'order_id', 'table_id'];
            foreach ($required as $field) {
                if (!isset($request[$field])) {
                    return Response::error("Missing required field: $field", 400);
                }
            }

            $sessionId = $this->ayceService->createAYCESession($request);
            return Response::success(['session_id' => $sessionId], 'AYCE session created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createAYCEReorder($request)
    {
        try {
            $required = ['session_id', 'order_id', 'items', 'total_amount'];
            foreach ($required as $field) {
                if (!isset($request[$field])) {
                    return Response::error("Missing required field: $field", 400);
                }
            }

            $reorderId = $this->ayceService->createAYCEReorder($request['session_id'], $request['order_id'], $request['items'], $request['total_amount']);
            if (!$reorderId) {
                return Response::error('Unable to create reorder (max reorders reached)', 400);
            }
            return Response::success(['reorder_id' => $reorderId], 'AYCE reorder created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function sendReorderToKitchen($request)
    {
        try {
            $reorderId = $request['id'];
            $kdsTicketId = $request['kds_ticket_id'];

            $this->ayceService->sendReorderToKitchen($reorderId, $kdsTicketId);
            return Response::success([], 'Reorder sent to kitchen successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function completeReorder($request)
    {
        try {
            $reorderId = $request['id'];

            $this->ayceService->completeReorder($reorderId);
            return Response::success([], 'Reorder completed successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getSessionReorders($request)
    {
        try {
            $sessionId = $request['id'];

            $reorders = $this->ayceService->getSessionReorders($sessionId);
            return Response::success($reorders, 'Session reorders retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function endSession($request)
    {
        try {
            $sessionId = $request['id'];
            $tenantId = $request['tenant_id'];

            $this->ayceService->endSession($sessionId, $tenantId);
            return Response::success([], 'AYCE session ended successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function checkSessionTimeouts($request)
    {
        try {
            $this->ayceService->checkSessionTimeouts();
            return Response::success([], 'Session timeouts checked successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}

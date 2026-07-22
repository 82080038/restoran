<?php

namespace App\Modules\Waitlist\Controllers;

use App\Core\Response;
use App\Modules\Waitlist\Services\WaitlistService;

class WaitlistController extends BaseController
{
    private $waitlistService;

    public function __construct()
    {
        $this->waitlistService = new WaitlistService();
    }

    public function getWaitlistEntries($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $status = $request['status'] ?? null;

            $entries = $this->waitlistService->getWaitlistEntries($tenantId, $branchId, $status);
            return Response::success($entries, 'Waitlist entries retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getWaitlistEntry($request)
    {
        try {
            $entryId = $request['id'];
            $tenantId = $request['tenant_id'];

            $entry = $this->waitlistService->getWaitlistEntry($entryId, $tenantId);
            if (!$entry) {
                return Response::error('Waitlist entry not found', 404);
            }
            return Response::success($entry, 'Waitlist entry retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createWaitlistEntry($request)
    {
        try {
            $required = ['tenant_id', 'branch_id', 'customer_name', 'phone', 'party_size'];
            foreach ($required as $field) {
                if (!isset($request[$field])) {
                    return Response::error("Missing required field: $field", 400);
                }
            }

            $entryId = $this->waitlistService->createWaitlistEntry($request);
            return Response::success(['entry_id' => $entryId], 'Waitlist entry created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateWaitlistEntry($request)
    {
        try {
            $entryId = $request['id'];
            $tenantId = $request['tenant_id'];

            $entry = $this->waitlistService->getWaitlistEntry($entryId, $tenantId);
            if (!$entry) {
                return Response::error('Waitlist entry not found', 404);
            }

            $this->waitlistService->updateWaitlistEntry($entryId, $tenantId, $request);
            return Response::success([], 'Waitlist entry updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function seatGuest($request)
    {
        try {
            $entryId = $request['id'];
            $tenantId = $request['tenant_id'];
            $tableId = $request['table_id'];
            $zoneId = $request['zone_id'] ?? null;

            $entry = $this->waitlistService->getWaitlistEntry($entryId, $tenantId);
            if (!$entry) {
                return Response::error('Waitlist entry not found', 404);
            }

            $this->waitlistService->seatGuest($entryId, $tenantId, $tableId, $zoneId);
            $this->waitlistService->updateQueuePositions($tenantId, $entry['branch_id']);
            return Response::success([], 'Guest seated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deleteWaitlistEntry($request)
    {
        try {
            $entryId = $request['id'];
            $tenantId = $request['tenant_id'];

            $entry = $this->waitlistService->getWaitlistEntry($entryId, $tenantId);
            if (!$entry) {
                return Response::error('Waitlist entry not found', 404);
            }

            $this->waitlistService->deleteWaitlistEntry($entryId, $tenantId);
            return Response::success([], 'Waitlist entry deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}

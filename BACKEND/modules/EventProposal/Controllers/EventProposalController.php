<?php

namespace App\Modules\EventProposal\Controllers;

use App\Core\Response;
use App\Modules\EventProposal\Services\EventProposalService;

class EventProposalController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new EventProposalService();
    }

    public function getProposals($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $status = $request['query']['status'] ?? null;

            $result = $this->service->getProposals($tenantId, $branchId, $status);
            return Response::success($result, 'Proposals retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getProposal($request)
    {
        try {
            $proposalId = $request['params']['id'] ?? $request['id'] ?? null;

            $result = $this->service->getProposalDetail($proposalId);
            if (!$result['proposal']) {
                return Response::notFound('Proposal not found');
            }
            return Response::success($result, 'Proposal retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createProposal($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            $data['created_by'] = $request['user_id'] ?? null;

            if (empty($data['client_name']) || empty($data['guest_count'])) {
                return Response::error('client_name and guest_count are required', 400);
            }

            $result = $this->service->createProposal($data);
            return Response::success($result, 'Proposal created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateProposalStatus($request)
    {
        try {
            $proposalId = $request['params']['id'] ?? $request['id'] ?? null;
            $status = $request['body']['status'] ?? null;

            if (!$status) {
                return Response::error('status is required', 400);
            }

            $result = $this->service->updateProposalStatus($proposalId, $status);
            return Response::success($result, 'Proposal status updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function recordDeposit($request)
    {
        try {
            $proposalId = $request['params']['id'] ?? $request['id'] ?? null;
            $amount = $request['body']['amount'] ?? null;

            if ($amount === null) {
                return Response::error('amount is required', 400);
            }

            $result = $this->service->recordDepositPayment($proposalId, $amount);
            return Response::success($result, 'Deposit recorded successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function convertToBEO($request)
    {
        try {
            $proposalId = $request['params']['id'] ?? $request['id'] ?? null;
            $createdBy = $request['user_id'] ?? null;

            $result = $this->service->convertToBEO($proposalId, $createdBy);
            return Response::success($result, 'BEO generated from proposal successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getBEOs($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $status = $request['query']['status'] ?? null;

            $result = $this->service->getBEOs($tenantId, $branchId, $status);
            return Response::success($result, 'BEOs retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getBEO($request)
    {
        try {
            $beoId = $request['params']['id'] ?? $request['id'] ?? null;

            $result = $this->service->getBEODetail($beoId);
            if (!$result['beo']) {
                return Response::notFound('BEO not found');
            }
            return Response::success($result, 'BEO retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function addBEOItem($request)
    {
        try {
            $beoId = $request['params']['id'] ?? $request['id'] ?? null;
            $item = $request['body'];

            if (empty($item['item_type']) || empty($item['description'])) {
                return Response::error('item_type and description are required', 400);
            }

            $result = $this->service->addBEOItem($beoId, $item);
            return Response::success($result, 'BEO item added successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function completeBEOItem($request)
    {
        try {
            $itemId = $request['params']['item_id'] ?? $request['item_id'] ?? null;

            $result = $this->service->completeBEOItem($itemId);
            return Response::success($result, 'BEO item marked as completed');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateBEOStatus($request)
    {
        try {
            $beoId = $request['params']['id'] ?? $request['id'] ?? null;
            $status = $request['body']['status'] ?? null;

            if (!$status) {
                return Response::error('status is required', 400);
            }

            $result = $this->service->updateBEOStatus($beoId, $status);
            return Response::success($result, 'BEO status updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}

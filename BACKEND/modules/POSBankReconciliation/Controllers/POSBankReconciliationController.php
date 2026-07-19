<?php

namespace App\Modules\POSBankReconciliation\Controllers;

use App\Core\Response;
use App\Modules\POSBankReconciliation\Services\POSBankReconciliationService;

class POSBankReconciliationController
{
    private $service;

    public function __construct()
    {
        $this->service = new POSBankReconciliationService();
    }

    public function getDeposits($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $dateFrom = $request['query']['date_from'] ?? null;
            $dateTo = $request['query']['date_to'] ?? null;
            $status = $request['query']['status'] ?? null;

            $deposits = $this->service->getDeposits($tenantId, $branchId, $dateFrom, $dateTo, $status);
            return Response::success($deposits, 'Deposits retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createDeposit($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;

            if (empty($data['deposit_date'])) {
                return Response::error('deposit_date is required', 400);
            }

            $result = $this->service->createDeposit($data);
            return Response::success($result, 'Deposit created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function matchDeposit($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $depositId = $request['params']['id'] ?? $request['id'] ?? null;
            $matchedBy = $request['user_id'] ?? null;

            $result = $this->service->matchDeposit($depositId, $matchedBy);
            return Response::success($result, 'Deposit matched successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function resolveDeposit($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $depositId = $request['params']['id'] ?? $request['id'] ?? null;
            $notes = $request['body']['notes'] ?? '';
            $resolvedBy = $request['user_id'] ?? null;

            $result = $this->service->resolveDeposit($depositId, $notes, $resolvedBy);
            return Response::success($result, 'Deposit resolved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getVarianceReport($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $dateFrom = $request['query']['date_from'] ?? date('Y-m-01');
            $dateTo = $request['query']['date_to'] ?? date('Y-m-d');

            $report = $this->service->getVarianceReport($tenantId, $branchId, $dateFrom, $dateTo);
            return Response::success($report, 'Variance report generated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function addMerchantFee($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;

            if (empty($data['transaction_date']) || empty($data['payment_method']) || empty($data['processor_name'])) {
                return Response::error('transaction_date, payment_method, and processor_name are required', 400);
            }

            $result = $this->service->addMerchantFee($data);
            return Response::success($result, 'Merchant fee recorded successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getMerchantFees($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $dateFrom = $request['query']['date_from'] ?? date('Y-m-01');
            $dateTo = $request['query']['date_to'] ?? date('Y-m-d');

            $result = $this->service->getMerchantFees($tenantId, $branchId, $dateFrom, $dateTo);
            return Response::success($result, 'Merchant fees retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createEODCloseout($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            $data['opened_by'] = $request['user_id'] ?? null;

            if (empty($data['closeout_date'])) {
                return Response::error('closeout_date is required', 400);
            }

            $result = $this->service->createEODCloseout($data);
            return Response::success($result, 'EOD closeout opened successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function closeEODCloseout($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $closeoutId = $request['params']['id'] ?? $request['id'] ?? null;
            $data = $request['body'];
            $data['closed_by'] = $request['user_id'] ?? null;

            $result = $this->service->closeEODCloseout($closeoutId, $data);
            return Response::success($result, 'EOD closeout closed successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getEODCloseouts($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $dateFrom = $request['query']['date_from'] ?? null;
            $dateTo = $request['query']['date_to'] ?? null;

            $result = $this->service->getEODCloseouts($tenantId, $branchId, $dateFrom, $dateTo);
            return Response::success($result, 'EOD closeouts retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}

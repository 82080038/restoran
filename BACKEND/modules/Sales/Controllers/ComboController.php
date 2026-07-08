<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('ComboService')) {
    require_once __DIR__ . '/../Services/ComboService.php';
}

class ComboController
{
    private $service;

    public function __construct()
    {
        $this->service = new ComboService();
    }

    /**
     * Create new combo
     */
    public function create()
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                Response::error('Invalid input data');
                return;
            }

            $result = $this->service->createCombo($input, $user['user_id'], $user['tenant_id']);

            if ($result['success']) {
                Response::success($result['data'], $result['message']);
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Combo creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get all combos for tenant
     */
    public function getAll($request)
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $params = $request['query'] ?? [];
            $isActive = $params['is_active'] ?? null;

            $result = $this->service->getCombos($user['tenant_id'], $isActive);

            if ($result['success']) {
                Response::success($result['data'], 'Combos retrieved successfully');
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Failed to get combos: ' . $e->getMessage());
        }
    }

    /**
     * Get specific combo
     */
    public function get($request)
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $comboId = $request['params']['id'] ?? null;

            if (!$comboId) {
                Response::error('Combo ID is required');
                return;
            }

            $result = $this->service->getCombo($comboId, $user['tenant_id']);

            if ($result['success']) {
                Response::success($result['data'], 'Combo retrieved successfully');
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Failed to get combo: ' . $e->getMessage());
        }
    }

    /**
     * Update combo
     */
    public function update($request)
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $comboId = $request['params']['id'] ?? null;
            $input = json_decode(file_get_contents("php://input"), true);

            if (!$comboId || !$input) {
                Response::error('Combo ID and input data are required');
                return;
            }

            $result = $this->service->updateCombo($comboId, $input, $user['tenant_id']);

            if ($result['success']) {
                Response::success($result['data'], $result['message']);
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Combo update failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete combo
     */
    public function delete($request)
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $comboId = $request['params']['id'] ?? null;

            if (!$comboId) {
                Response::error('Combo ID is required');
                return;
            }

            $result = $this->service->deleteCombo($comboId, $user['tenant_id']);

            if ($result['success']) {
                Response::success([], $result['message']);
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Combo deletion failed: ' . $e->getMessage());
        }
    }

    /**
     * Calculate combo price for order
     */
    public function calculatePrice($request)
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                Response::error('Invalid input data');
                return;
            }

            $comboId = $input['combo_id'] ?? null;
            $quantities = $input['quantities'] ?? [];

            if (!$comboId) {
                Response::error('Combo ID is required');
                return;
            }

            $result = $this->service->calculateComboPrice($comboId, $quantities, $user['tenant_id']);

            if ($result['success']) {
                Response::success($result['data'], 'Price calculated successfully');
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Price calculation failed: ' . $e->getMessage());
        }
    }
}

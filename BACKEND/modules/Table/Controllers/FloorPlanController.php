<?php

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';

/**
 * Floor Plan Controller
 * Manages visual restaurant floor layout: floors, zones, tables, chairs
 */
class FloorPlanController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // === FLOORS ===

    /**
     * GET /api/v1/floor-plan/floors
     */
    public function getFloors($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
            $branchId = $request['query']['branch_id'] ?? null;

            $sql = "SELECT * FROM floors WHERE tenant_id = ? AND LOWER(status) = 'active'";
            $params = [$tenantId];
            if ($branchId) { $sql .= " AND branch_id = ?"; $params[] = $branchId; }
            $sql .= " ORDER BY sort_order ASC, floor_level ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $floors = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($floors, 'Floors retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * POST /api/v1/floor-plan/floors
     */
    public function createFloor($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $tenantId = $payload['tenant_id'] ?? 1;

            if (empty($body['floor_name'])) {
                return Response::error('floor_name is required', 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO floors (tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, description, sort_order, status, canvas_width, canvas_height, grid_enabled, grid_size)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tenantId,
                $body['branch_id'] ?? null,
                $body['floor_code'] ?? null,
                $body['floor_name'],
                (int)($body['floor_level'] ?? 0),
                $body['floor_type'] ?? 'dining',
                $body['description'] ?? null,
                (int)($body['sort_order'] ?? 0),
                (int)($body['canvas_width'] ?? 1200),
                (int)($body['canvas_height'] ?? 800),
                (int)($body['grid_enabled'] ?? 1),
                (int)($body['grid_size'] ?? 20),
            ]);

            return Response::success(['floor_id' => (int)$pdo->lastInsertId()], 'Floor created');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * PUT /api/v1/floor-plan/floors/{id}
     */
    public function updateFloor($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $floorId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];

            $fields = [];
            $params = [];
            $allowed = ['floor_code', 'floor_name', 'floor_level', 'floor_type', 'description', 'sort_order', 'status', 'canvas_width', 'canvas_height', 'background_image', 'grid_enabled', 'grid_size'];

            foreach ($allowed as $f) {
                if (array_key_exists($f, $body)) {
                    $fields[] = "$f = ?";
                    $params[] = $body[$f];
                }
            }

            if (empty($fields)) {
                return Response::error('No fields to update', 400);
            }

            $fields[] = "updated_at = NOW()";
            $params[] = $floorId;
            $params[] = $tenantId;

            $stmt = $pdo->prepare("UPDATE floors SET " . implode(', ', $fields) . " WHERE floor_id = ? AND tenant_id = ?");
            $stmt->execute($params);

            return Response::success(['floor_id' => (int)$floorId], 'Floor updated');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * DELETE /api/v1/floor-plan/floors/{id}
     */
    public function deleteFloor($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $floorId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("UPDATE floors SET status = 'inactive' WHERE floor_id = ? AND tenant_id = ?");
            $stmt->execute([$floorId, $tenantId]);

            if ($stmt->rowCount() === 0) {
                return Response::notFound('Floor not found');
            }

            return Response::success([], 'Floor deleted');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    // === ZONES ===

    /**
     * GET /api/v1/floor-plan/zones?floor_id=
     */
    public function getZones($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
            $floorId = $request['query']['floor_id'] ?? null;

            $sql = "SELECT * FROM zones WHERE tenant_id = ?";
            $params = [$tenantId];
            if ($floorId) { $sql .= " AND floor_id = ?"; $params[] = $floorId; }
            $sql .= " ORDER BY sort_order ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $zones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($zones, 'Zones retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * POST /api/v1/floor-plan/zones
     */
    public function createZone($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $tenantId = $payload['tenant_id'] ?? 1;

            if (empty($body['zone_name']) || empty($body['floor_id'])) {
                return Response::error('zone_name and floor_id are required', 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO zones (tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, description, capacity, sort_order, status, zone_color, pos_x, pos_y, zone_width, zone_height)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tenantId,
                $body['branch_id'] ?? null,
                $body['floor_id'],
                $body['zone_code'] ?? null,
                $body['zone_name'],
                $body['zone_type'] ?? 'dining',
                $body['service_type'] ?? 'dine_in',
                $body['description'] ?? null,
                (int)($body['capacity'] ?? 0),
                (int)($body['sort_order'] ?? 0),
                $body['zone_color'] ?? '#e8f4f8',
                (float)($body['pos_x'] ?? 0),
                (float)($body['pos_y'] ?? 0),
                (float)($body['zone_width'] ?? 400),
                (float)($body['zone_height'] ?? 300),
            ]);

            return Response::success(['zone_id' => (int)$pdo->lastInsertId()], 'Zone created');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * PUT /api/v1/floor-plan/zones/{id}
     */
    public function updateZone($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $zoneId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];

            $fields = [];
            $params = [];
            $allowed = ['zone_code', 'zone_name', 'zone_type', 'service_type', 'description', 'capacity', 'sort_order', 'status', 'zone_color', 'pos_x', 'pos_y', 'zone_width', 'zone_height'];

            foreach ($allowed as $f) {
                if (array_key_exists($f, $body)) {
                    $fields[] = "$f = ?";
                    $params[] = $body[$f];
                }
            }

            if (empty($fields)) {
                return Response::error('No fields to update', 400);
            }

            $fields[] = "updated_at = NOW()";
            $params[] = $zoneId;
            $params[] = $tenantId;

            $stmt = $pdo->prepare("UPDATE zones SET " . implode(', ', $fields) . " WHERE zone_id = ? AND tenant_id = ?");
            $stmt->execute($params);

            return Response::success(['zone_id' => (int)$zoneId], 'Zone updated');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * DELETE /api/v1/floor-plan/zones/{id}
     */
    public function deleteZone($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $zoneId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("UPDATE zones SET status = 'inactive' WHERE zone_id = ? AND tenant_id = ?");
            $stmt->execute([$zoneId, $tenantId]);

            if ($stmt->rowCount() === 0) {
                return Response::notFound('Zone not found');
            }

            return Response::success([], 'Zone deleted');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    // === TABLES (layout) ===

    /**
     * GET /api/v1/floor-plan/tables?floor_id=&zone_id=
     * Returns tables with their chairs for floor plan rendering
     */
    public function getTables($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
            $floorId = $request['query']['floor_id'] ?? null;
            $zoneId = $request['query']['zone_id'] ?? null;
            $branchId = $request['query']['branch_id'] ?? null;

            $sql = "SELECT * FROM tables WHERE tenant_id = ? AND deleted_at IS NULL";
            $params = [$tenantId];

            if ($floorId) { $sql .= " AND floor_id = ?"; $params[] = $floorId; }
            if ($zoneId) { $sql .= " AND zone_id = ?"; $params[] = $zoneId; }
            if ($branchId) { $sql .= " AND branch_id = ?"; $params[] = $branchId; }
            $sql .= " ORDER BY table_number ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Load chairs for each table
            $tableIds = array_column($tables, 'table_id');
            $chairsMap = [];
            if (!empty($tableIds)) {
                $placeholders = implode(',', array_fill(0, count($tableIds), '?'));
                $stmt = $pdo->prepare("SELECT * FROM table_chairs WHERE table_id IN ($placeholders) AND is_active = 1 ORDER BY chair_number ASC");
                $stmt->execute($tableIds);
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $chairsMap[$row['table_id']][] = $row;
                }
            }

            foreach ($tables as &$table) {
                $table['chairs'] = $chairsMap[$table['table_id']] ?? [];
            }

            return Response::success($tables, 'Tables retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * POST /api/v1/floor-plan/tables
     * Create a table with position and shape
     */
    public function createTable($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $tenantId = $payload['tenant_id'] ?? 1;

            if (empty($body['table_number'])) {
                return Response::error('table_number is required', 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO tables (tenant_id, branch_id, floor_id, zone_id, table_number, table_name, capacity, area, status, table_shape, pos_x, pos_y, table_width, table_height, table_rotation, chair_count)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'AVAILABLE', ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tenantId,
                $body['branch_id'] ?? null,
                $body['floor_id'] ?? null,
                $body['zone_id'] ?? null,
                $body['table_number'],
                $body['table_name'] ?? null,
                (int)($body['capacity'] ?? 4),
                $body['area'] ?? null,
                $body['table_shape'] ?? 'rectangle',
                (float)($body['pos_x'] ?? 0),
                (float)($body['pos_y'] ?? 0),
                (float)($body['table_width'] ?? 80),
                (float)($body['table_height'] ?? 80),
                (float)($body['table_rotation'] ?? 0),
                (int)($body['chair_count'] ?? 4),
            ]);

            $tableId = (int)$pdo->lastInsertId();

            // Auto-generate chairs if positions provided
            if (!empty($body['chairs'])) {
                $this->saveChairs($pdo, $tableId, $body['chairs']);
            } else {
                $this->autoGenerateChairs($pdo, $tableId, $body['table_shape'] ?? 'rectangle', (int)($body['chair_count'] ?? 4), (float)($body['table_width'] ?? 80), (float)($body['table_height'] ?? 80));
            }

            return Response::success(['table_id' => $tableId], 'Table created');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * PUT /api/v1/floor-plan/tables/{id}
     * Update table position, shape, and chairs
     */
    public function updateTable($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tableId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];

            $fields = [];
            $params = [];
            $allowed = ['table_number', 'table_name', 'capacity', 'area', 'status', 'floor_id', 'zone_id', 'table_shape', 'pos_x', 'pos_y', 'table_width', 'table_height', 'table_rotation', 'chair_count', 'layout_data'];

            foreach ($allowed as $f) {
                if (array_key_exists($f, $body)) {
                    $fields[] = "$f = ?";
                    $params[] = $body[$f];
                }
            }

            if (!empty($fields)) {
                $fields[] = "updated_at = NOW()";
                $params[] = $tableId;
                $params[] = $tenantId;

                $stmt = $pdo->prepare("UPDATE tables SET " . implode(', ', $fields) . " WHERE table_id = ? AND tenant_id = ?");
                $stmt->execute($params);
            }

            // Update chairs if provided
            if (isset($body['chairs'])) {
                $this->saveChairs($pdo, $tableId, $body['chairs']);
            }

            return Response::success(['table_id' => (int)$tableId], 'Table updated');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * PUT /api/v1/floor-plan/tables/{id}/position
     * Quick position update (drag-and-drop)
     */
    public function updateTablePosition($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tableId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];

            $stmt = $pdo->prepare("UPDATE tables SET pos_x = ?, pos_y = ?, table_rotation = ?, updated_at = NOW() WHERE table_id = ? AND tenant_id = ?");
            $stmt->execute([
                (float)($body['pos_x'] ?? 0),
                (float)($body['pos_y'] ?? 0),
                (float)($body['rotation'] ?? 0),
                $tableId,
                $tenantId
            ]);

            return Response::success(['table_id' => (int)$tableId], 'Position updated');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * DELETE /api/v1/floor-plan/tables/{id}
     */
    public function deleteTable($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tableId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("UPDATE tables SET deleted_at = NOW() WHERE table_id = ? AND tenant_id = ?");
            $stmt->execute([$tableId, $tenantId]);

            // Deactivate chairs
            $pdo->prepare("UPDATE table_chairs SET is_active = 0 WHERE table_id = ?")->execute([$tableId]);

            return Response::success([], 'Table deleted');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * GET /api/v1/floor-plan/layout?floor_id=
     * Get complete floor plan: floor + zones + tables + chairs in one call
     */
    public function getLayout($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
            $floorId = $request['query']['floor_id'] ?? null;

            if (!$floorId) {
                return Response::error('floor_id is required', 400);
            }

            // Get floor
            $stmt = $pdo->prepare("SELECT * FROM floors WHERE floor_id = ? AND tenant_id = ? AND LOWER(status) = 'active'");
            $stmt->execute([$floorId, $tenantId]);
            $floor = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$floor) {
                return Response::notFound('Floor not found');
            }

            // Get zones
            $stmt = $pdo->prepare("SELECT * FROM zones WHERE floor_id = ? AND tenant_id = ? AND LOWER(status) = 'active' ORDER BY sort_order");
            $stmt->execute([$floorId, $tenantId]);
            $zones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get tables
            $stmt = $pdo->prepare("SELECT * FROM tables WHERE floor_id = ? AND tenant_id = ? AND deleted_at IS NULL ORDER BY table_number");
            $stmt->execute([$floorId, $tenantId]);
            $tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get chairs
            $tableIds = array_column($tables, 'table_id');
            $chairsMap = [];
            if (!empty($tableIds)) {
                $placeholders = implode(',', array_fill(0, count($tableIds), '?'));
                $stmt = $pdo->prepare("SELECT * FROM table_chairs WHERE table_id IN ($placeholders) AND is_active = 1 ORDER BY chair_number");
                $stmt->execute($tableIds);
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $chairsMap[$row['table_id']][] = $row;
                }
            }

            foreach ($tables as &$table) {
                $table['chairs'] = $chairsMap[$table['table_id']] ?? [];
            }

            return Response::success([
                'floor' => $floor,
                'zones' => $zones,
                'tables' => $tables,
            ], 'Floor plan layout retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * POST /api/v1/floor-plan/layout/save
     * Save entire floor plan layout in one request (bulk update)
     */
    public function saveLayout($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];
            $floorId = $body['floor_id'] ?? 0;

            if (!$floorId) {
                return Response::error('floor_id is required', 400);
            }

            $pdo->beginTransaction();

            // Update floor canvas settings
            if (!empty($body['floor'])) {
                $stmt = $pdo->prepare("UPDATE floors SET canvas_width = ?, canvas_height = ?, background_image = ?, grid_enabled = ?, grid_size = ?, updated_at = NOW() WHERE floor_id = ? AND tenant_id = ?");
                $stmt->execute([
                    (int)($body['floor']['canvas_width'] ?? 1200),
                    (int)($body['floor']['canvas_height'] ?? 800),
                    $body['floor']['background_image'] ?? null,
                    (int)($body['floor']['grid_enabled'] ?? 1),
                    (int)($body['floor']['grid_size'] ?? 20),
                    $floorId,
                    $tenantId
                ]);
            }

            // Update zones positions
            if (!empty($body['zones'])) {
                $stmt = $pdo->prepare("UPDATE zones SET pos_x = ?, pos_y = ?, zone_width = ?, zone_height = ?, zone_color = ?, updated_at = NOW() WHERE zone_id = ? AND tenant_id = ?");
                foreach ($body['zones'] as $zone) {
                    $stmt->execute([
                        (float)($zone['pos_x'] ?? 0),
                        (float)($zone['pos_y'] ?? 0),
                        (float)($zone['zone_width'] ?? 400),
                        (float)($zone['zone_height'] ?? 300),
                        $zone['zone_color'] ?? '#e8f4f8',
                        $zone['zone_id'],
                        $tenantId
                    ]);
                }
            }

            // Update tables positions
            if (!empty($body['tables'])) {
                $stmt = $pdo->prepare("UPDATE tables SET pos_x = ?, pos_y = ?, table_width = ?, table_height = ?, table_rotation = ?, table_shape = ?, chair_count = ?, updated_at = NOW() WHERE table_id = ? AND tenant_id = ?");
                foreach ($body['tables'] as $table) {
                    $stmt->execute([
                        (float)($table['pos_x'] ?? 0),
                        (float)($table['pos_y'] ?? 0),
                        (float)($table['table_width'] ?? 80),
                        (float)($table['table_height'] ?? 80),
                        (float)($table['table_rotation'] ?? 0),
                        $table['table_shape'] ?? 'rectangle',
                        (int)($table['chair_count'] ?? 4),
                        $table['table_id'],
                        $tenantId
                    ]);

                    // Update chairs if provided
                    if (!empty($table['chairs'])) {
                        $this->saveChairs($pdo, $table['table_id'], $table['chairs']);
                    }
                }
            }

            $pdo->commit();

            return Response::success(['floor_id' => (int)$floorId], 'Floor plan layout saved');
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    // === HELPERS ===

    private function saveChairs($pdo, $tableId, $chairs)
    {
        // Delete existing chairs
        $pdo->prepare("DELETE FROM table_chairs WHERE table_id = ?")->execute([$tableId]);

        // Insert new chairs
        $stmt = $pdo->prepare("
            INSERT INTO table_chairs (table_id, chair_number, chair_shape, pos_x, pos_y, chair_rotation, is_active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        foreach ($chairs as $chair) {
            $stmt->execute([
                $tableId,
                (int)($chair['chair_number'] ?? 0),
                $chair['chair_shape'] ?? 'square',
                (float)($chair['pos_x'] ?? 0),
                (float)($chair['pos_y'] ?? 0),
                (float)($chair['chair_rotation'] ?? 0),
            ]);
        }
    }

    private function autoGenerateChairs($pdo, $tableId, $shape, $chairCount, $width, $height)
    {
        $chairs = [];
        $cx = $width / 2;
        $cy = $height / 2;

        if ($shape === 'round') {
            $radius = max($width, $height) / 2 + 20;
            for ($i = 0; $i < $chairCount; $i++) {
                $angle = (2 * M_PI * $i) / $chairCount - M_PI / 2;
                $chairs[] = [
                    'chair_number' => $i + 1,
                    'chair_shape' => 'square',
                    'pos_x' => $cx + $radius * cos($angle) - 12,
                    'pos_y' => $cy + $radius * sin($angle) - 12,
                    'chair_rotation' => rad2deg($angle) + 90,
                ];
            }
        } else {
            // Rectangle: distribute chairs around perimeter
            $perSide = (int)ceil($chairCount / 4);
            $spacing = $width / ($perSide + 1);
            $idx = 0;
            // Top
            for ($i = 0; $i < $perSide && $idx < $chairCount; $i++, $idx++) {
                $chairs[] = ['chair_number' => $idx + 1, 'chair_shape' => 'square', 'pos_x' => $spacing * ($i + 1) - 12, 'pos_y' => -22, 'chair_rotation' => 180];
            }
            // Bottom
            for ($i = 0; $i < $perSide && $idx < $chairCount; $i++, $idx++) {
                $chairs[] = ['chair_number' => $idx + 1, 'chair_shape' => 'square', 'pos_x' => $spacing * ($i + 1) - 12, 'pos_y' => $height + 6, 'chair_rotation' => 0];
            }
            // Left
            $vspacing = $height / ($perSide + 1);
            for ($i = 0; $i < $perSide && $idx < $chairCount; $i++, $idx++) {
                $chairs[] = ['chair_number' => $idx + 1, 'chair_shape' => 'square', 'pos_x' => -22, 'pos_y' => $vspacing * ($i + 1) - 12, 'chair_rotation' => 90];
            }
            // Right
            for ($i = 0; $i < $perSide && $idx < $chairCount; $i++, $idx++) {
                $chairs[] = ['chair_number' => $idx + 1, 'chair_shape' => 'square', 'pos_x' => $width + 6, 'pos_y' => $vspacing * ($i + 1) - 12, 'chair_rotation' => 270];
            }
        }

        $this->saveChairs($pdo, $tableId, $chairs);
    }
}

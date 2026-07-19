<?php

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';

/**
 * Bill Split Controller
 * Manages table groups and bill splitting/merging
 *
 * Concepts:
 * - A table can have multiple groups (e.g., 2 friends sharing a 4-seat table)
 * - Each group has their own bill
 * - Order items can be assigned to specific groups or split across groups
 * - Bills can be merged back into one
 */
class BillSplitController
{
    private $db;

    private $groupColors = ['#667eea', '#e74c3c', '#27ae60', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#3498db'];

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * GET /api/v1/bill-split/tables/{tableId}/groups
     * Get all groups at a table with their bills
     */
    public function getTableGroups($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tableId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;

            // Get groups
            $stmt = $pdo->prepare("
                SELECT tg.*, t.table_number, t.table_name, t.capacity, t.chair_count, t.status as table_status
                FROM table_groups tg
                INNER JOIN tables t ON tg.table_id = t.table_id
                WHERE tg.table_id = ? AND tg.tenant_id = ? AND tg.status = 'active'
                ORDER BY tg.started_at ASC
            ");
            $stmt->execute([$tableId, $tenantId]);
            $groups = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get members and bills for each group
            foreach ($groups as &$group) {
                // Members
                $stmt = $pdo->prepare("SELECT * FROM table_group_members WHERE group_id = ? ORDER BY chair_number ASC");
                $stmt->execute([$group['group_id']]);
                $group['members'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                // Bills
                $stmt = $pdo->prepare("SELECT * FROM bill_splits WHERE group_id = ? AND payment_status != 'voided' ORDER BY created_at DESC");
                $stmt->execute([$group['group_id']]);
                $group['bills'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Get table chairs
            $stmt = $pdo->prepare("SELECT * FROM table_chairs WHERE table_id = ? AND is_active = 1 ORDER BY chair_number ASC");
            $stmt->execute([$tableId]);
            $chairs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success([
                'table_id' => (int)$tableId,
                'groups' => $groups,
                'chairs' => $chairs,
                'group_count' => count($groups),
            ], 'Table groups retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * POST /api/v1/bill-split/groups
     * Create a new group at a table
     */
    public function createGroup($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $tenantId = $payload['tenant_id'] ?? 1;

            if (empty($body['table_id'])) {
                return Response::error('table_id is required', 400);
            }

            $tableId = (int)$body['table_id'];

            // Count existing groups for color assignment
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM table_groups WHERE table_id = ? AND status = 'active'");
            $stmt->execute([$tableId]);
            $groupIndex = (int)$stmt->fetchColumn();
            $color = $this->groupColors[$groupIndex % count($this->groupColors)];

            // Create group
            $stmt = $pdo->prepare("
                INSERT INTO table_groups (tenant_id, branch_id, table_id, group_name, group_color, group_size, status, started_at)
                VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([
                $tenantId,
                $body['branch_id'] ?? null,
                $tableId,
                $body['group_name'] ?? ('Group ' . chr(65 + $groupIndex)),
                $body['group_color'] ?? $color,
                (int)($body['group_size'] ?? 1),
            ]);

            $groupId = (int)$pdo->lastInsertId();

            // Add members (chair assignments)
            if (!empty($body['chairs'])) {
                $stmt = $pdo->prepare("INSERT INTO table_group_members (group_id, chair_id, chair_number, member_name) VALUES (?, ?, ?, ?)");
                foreach ($body['chairs'] as $chair) {
                    $stmt->execute([
                        $groupId,
                        $chair['chair_id'] ?? null,
                        (int)($chair['chair_number'] ?? 0),
                        $chair['member_name'] ?? null,
                    ]);
                }
            }

            // Create initial bill for the group
            $billNumber = 'BILL-' . $tableId . '-' . chr(65 + $groupIndex);
            $stmt = $pdo->prepare("
                INSERT INTO bill_splits (tenant_id, branch_id, table_id, group_id, bill_number, bill_type, payment_status)
                VALUES (?, ?, ?, ?, ?, 'group', 'unpaid')
            ");
            $stmt->execute([$tenantId, $body['branch_id'] ?? null, $tableId, $groupId, $billNumber]);
            $billId = (int)$pdo->lastInsertId();

            // Update table status to OCCUPIED
            $pdo->prepare("UPDATE tables SET status = 'OCCUPIED', updated_at = NOW() WHERE table_id = ? AND tenant_id = ?")
                ->execute([$tableId, $tenantId]);

            // Log history
            $this->logHistory($pdo, $tableId, 'CREATE_GROUP', 'Created group ' . ($groupIndex + 1), $payload['user_id'] ?? null);

            return Response::success([
                'group_id' => $groupId,
                'bill_id' => $billId,
                'group_color' => $color,
            ], 'Group created');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * PUT /api/v1/bill-split/groups/{id}
     * Update group (name, color, members)
     */
    public function updateGroup($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $groupId = $request['id'] ?? 0;
            $body = $request['body'] ?? [];

            $fields = [];
            $params = [];
            $allowed = ['group_name', 'group_color', 'group_size', 'status', 'ended_at'];

            foreach ($allowed as $f) {
                if (array_key_exists($f, $body)) {
                    $fields[] = "$f = ?";
                    $params[] = $body[$f];
                }
            }

            if (!empty($fields)) {
                $fields[] = "updated_at = NOW()";
                $params[] = $groupId;
                $stmt = $pdo->prepare("UPDATE table_groups SET " . implode(', ', $fields) . " WHERE group_id = ?");
                $stmt->execute($params);
            }

            // Update members if provided
            if (isset($body['chairs'])) {
                $pdo->prepare("DELETE FROM table_group_members WHERE group_id = ?")->execute([$groupId]);
                $stmt = $pdo->prepare("INSERT INTO table_group_members (group_id, chair_id, chair_number, member_name) VALUES (?, ?, ?, ?)");
                foreach ($body['chairs'] as $chair) {
                    $stmt->execute([
                        $groupId,
                        $chair['chair_id'] ?? null,
                        (int)($chair['chair_number'] ?? 0),
                        $chair['member_name'] ?? null,
                    ]);
                }
            }

            return Response::success(['group_id' => (int)$groupId], 'Group updated');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * DELETE /api/v1/bill-split/groups/{id}
     * Close a group (end session)
     */
    public function closeGroup($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $groupId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;

            // Get table_id before closing
            $stmt = $pdo->prepare("SELECT table_id FROM table_groups WHERE group_id = ?");
            $stmt->execute([$groupId]);
            $tableId = $stmt->fetchColumn();

            // Close group
            $pdo->prepare("UPDATE table_groups SET status = 'closed', ended_at = NOW(), updated_at = NOW() WHERE group_id = ?")
                ->execute([$groupId]);

            // Check if any active groups remain
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM table_groups WHERE table_id = ? AND status = 'active'");
            $stmt->execute([$tableId]);
            $activeCount = (int)$stmt->fetchColumn();

            if ($activeCount === 0) {
                // Set table to cleaning
                $pdo->prepare("UPDATE tables SET status = 'CLEANING', updated_at = NOW() WHERE table_id = ? AND tenant_id = ?")
                    ->execute([$tableId, $tenantId]);
            }

            $this->logHistory($pdo, $tableId, 'CLOSE_GROUP', "Closed group {$groupId}", $payload['user_id'] ?? null);

            return Response::success([
                'group_id' => (int)$groupId,
                'remaining_groups' => $activeCount,
                'table_status' => $activeCount === 0 ? 'CLEANING' : 'OCCUPIED',
            ], 'Group closed');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * POST /api/v1/bill-split/bills/{billId}/items
     * Assign an order item to a bill (or split across bills)
     */
    public function assignItemToBill($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $billId = $request['id'] ?? 0;
            $body = $request['body'] ?? [];

            if (empty($body['order_item_id'])) {
                return Response::error('order_item_id is required', 400);
            }

            $orderItemId = (int)$body['order_item_id'];
            $splitType = $body['split_type'] ?? 'full'; // full, partial
            $splitRatio = (float)($body['split_ratio'] ?? 1.0);

            // Get order item details
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_item_id = ?");
            $stmt->execute([$orderItemId]);
            $orderItem = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$orderItem) {
                return Response::notFound('Order item not found');
            }

            $quantity = (float)$orderItem['quantity'];
            $unitPrice = (float)$orderItem['unit_price'];
            $totalPrice = $unitPrice * $quantity * $splitRatio;

            // Insert bill split item
            $stmt = $pdo->prepare("
                INSERT INTO bill_split_items (bill_id, order_item_id, product_name, quantity, unit_price, total_price, split_type, split_ratio, assigned_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $billId,
                $orderItemId,
                $orderItem['product_name'] ?? null,
                $quantity,
                $unitPrice,
                $totalPrice,
                $splitType,
                $splitRatio,
                $body['assigned_by'] ?? 'manual',
            ]);

            // Recalculate bill totals
            $this->recalculateBill($pdo, $billId);

            $this->logHistory($pdo, $body['table_id'] ?? 0, 'ASSIGN_ITEM', "Assigned item to bill {$billId}", $payload['user_id'] ?? null);

            return Response::success([
                'bill_id' => (int)$billId,
                'order_item_id' => $orderItemId,
                'total_price' => $totalPrice,
            ], 'Item assigned to bill');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * DELETE /api/v1/bill-split/bills/{billId}/items/{itemId}
     * Remove an item from a bill
     */
    public function removeItemFromBill($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $billId = $request['id'] ?? 0;
            $itemId = $request['item_id'] ?? 0;

            $stmt = $pdo->prepare("DELETE FROM bill_split_items WHERE item_id = ? AND bill_id = ?");
            $stmt->execute([$itemId, $billId]);

            $this->recalculateBill($pdo, $billId);

            return Response::success(['bill_id' => (int)$billId], 'Item removed from bill');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * GET /api/v1/bill-split/bills/{billId}
     * Get bill details with items
     */
    public function getBill($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $billId = $request['id'] ?? 0;

            $stmt = $pdo->prepare("SELECT * FROM bill_splits WHERE bill_id = ?");
            $stmt->execute([$billId]);
            $bill = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$bill) {
                return Response::notFound('Bill not found');
            }

            $stmt = $pdo->prepare("SELECT * FROM bill_split_items WHERE bill_id = ? ORDER BY created_at ASC");
            $stmt->execute([$billId]);
            $bill['items'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($bill, 'Bill retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * POST /api/v1/bill-split/merge
     * Merge multiple bills into one
     */
    public function mergeBills($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $billIds = $body['bill_ids'] ?? [];
            $tenantId = $payload['tenant_id'] ?? 1;

            if (count($billIds) < 2) {
                return Response::error('At least 2 bills required to merge', 400);
            }

            $pdo->beginTransaction();

            // Get first bill as target
            $targetBillId = (int)$billIds[0];
            $stmt = $pdo->prepare("SELECT * FROM bill_splits WHERE bill_id = ?");
            $stmt->execute([$targetBillId]);
            $targetBill = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$targetBill) {
                $pdo->rollBack();
                return Response::notFound('Target bill not found');
            }

            // Move items from other bills to target bill
            $stmt = $pdo->prepare("UPDATE bill_split_items SET bill_id = ? WHERE bill_id = ?");
            for ($i = 1; $i < count($billIds); $i++) {
                $stmt->execute([$targetBillId, (int)$billIds[$i]]);
                // Void the source bill
                $pdo->prepare("UPDATE bill_splits SET payment_status = 'voided', updated_at = NOW() WHERE bill_id = ?")
                    ->execute([(int)$billIds[$i]]);
            }

            // Recalculate target bill
            $this->recalculateBill($pdo, $targetBillId);

            $pdo->commit();

            $this->logHistory($pdo, $targetBill['table_id'], 'MERGE_BILLS', "Merged " . count($billIds) . " bills into {$targetBillId}", $payload['user_id'] ?? null);

            return Response::success([
                'merged_bill_id' => $targetBillId,
                'merged_count' => count($billIds),
            ], 'Bills merged successfully');
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * POST /api/v1/bill-split/bills/{billId}/split
     * Split a bill into multiple bills (e.g., split equally)
     */
    public function splitBill($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $billId = $request['id'] ?? 0;
            $body = $request['body'] ?? [];
            $splitCount = (int)($body['split_count'] ?? 2);
            $splitMode = $body['split_mode'] ?? 'equal'; // equal, items, custom
            $tenantId = $payload['tenant_id'] ?? 1;

            if ($splitCount < 2) {
                return Response::error('Split count must be at least 2', 400);
            }

            // Get original bill
            $stmt = $pdo->prepare("SELECT * FROM bill_splits WHERE bill_id = ?");
            $stmt->execute([$billId]);
            $originalBill = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$originalBill) {
                return Response::notFound('Bill not found');
            }

            // Get items
            $stmt = $pdo->prepare("SELECT * FROM bill_split_items WHERE bill_id = ?");
            $stmt->execute([$billId]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $pdo->beginTransaction();

            if ($splitMode === 'equal') {
                // Split each item equally across new bills
                $newBillIds = [];
                for ($i = 0; $i < $splitCount; $i++) {
                    $billNumber = $originalBill['bill_number'] . '-S' . ($i + 1);
                    $stmt = $pdo->prepare("
                        INSERT INTO bill_splits (tenant_id, branch_id, table_id, group_id, bill_number, bill_type, payment_status)
                        VALUES (?, ?, ?, ?, ?, 'split', 'unpaid')
                    ");
                    $stmt->execute([
                        $originalBill['tenant_id'],
                        $originalBill['branch_id'],
                        $originalBill['table_id'],
                        $originalBill['group_id'],
                        $billNumber,
                    ]);
                    $newBillIds[] = (int)$pdo->lastInsertId();
                }

                // Split items equally
                $ratio = 1.0 / $splitCount;
                foreach ($items as $item) {
                    foreach ($newBillIds as $newBillId) {
                        $stmt = $pdo->prepare("
                            INSERT INTO bill_split_items (bill_id, order_item_id, product_name, quantity, unit_price, total_price, split_type, split_ratio, assigned_by)
                            VALUES (?, ?, ?, ?, ?, ?, 'partial', ?, 'auto')
                        ");
                        $stmt->execute([
                            $newBillId,
                            $item['order_item_id'],
                            $item['product_name'],
                            $item['quantity'] * $ratio,
                            $item['unit_price'],
                            $item['total_price'] * $ratio,
                            $ratio,
                        ]);
                    }
                }

                // Void original bill
                $pdo->prepare("UPDATE bill_splits SET payment_status = 'voided', updated_at = NOW() WHERE bill_id = ?")
                    ->execute([$billId]);

                // Recalculate new bills
                foreach ($newBillIds as $newBillId) {
                    $this->recalculateBill($pdo, $newBillId);
                }

                $pdo->commit();

                $this->logHistory($pdo, $originalBill['table_id'], 'SPLIT_BILL', "Split bill {$billId} into {$splitCount} bills (equal)", $payload['user_id'] ?? null);

                return Response::success([
                    'original_bill_id' => (int)$billId,
                    'new_bill_ids' => $newBillIds,
                    'split_mode' => 'equal',
                    'per_person_total' => round((float)$originalBill['total'] / $splitCount, 2),
                ], 'Bill split equally');
            } else {
                $pdo->rollBack();
                return Response::error('Only equal split mode is supported via API. Use items mode from frontend.', 400);
            }
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * PATCH /api/v1/bill-split/bills/{billId}/payment
     * Mark bill as paid
     */
    public function markBillPaid($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $billId = $request['id'] ?? 0;
            $body = $request['body'] ?? [];

            $stmt = $pdo->prepare("UPDATE bill_splits SET payment_status = 'paid', payment_method = ?, paid_at = NOW(), updated_at = NOW() WHERE bill_id = ?");
            $stmt->execute([
                $body['payment_method'] ?? 'cash',
                $billId,
            ]);

            // Check if all bills for the table are paid
            $stmt = $pdo->prepare("SELECT table_id FROM bill_splits WHERE bill_id = ?");
            $stmt->execute([$billId]);
            $tableId = $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bill_splits WHERE table_id = ? AND payment_status = 'unpaid'");
            $stmt->execute([$tableId]);
            $unpaidCount = (int)$stmt->fetchColumn();

            $this->logHistory($pdo, $tableId, 'BILL_PAID', "Bill {$billId} paid via " . ($body['payment_method'] ?? 'cash'), $payload['user_id'] ?? null);

            return Response::success([
                'bill_id' => (int)$billId,
                'unpaid_bills_remaining' => $unpaidCount,
            ], 'Bill marked as paid');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * GET /api/v1/bill-split/tables/{tableId}/summary
     * Get complete bill summary for a table
     */
    public function getTableSummary($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tableId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;

            // Get table info
            $stmt = $pdo->prepare("SELECT * FROM tables WHERE table_id = ? AND tenant_id = ? AND deleted_at IS NULL");
            $stmt->execute([$tableId, $tenantId]);
            $table = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$table) {
                return Response::notFound('Table not found');
            }

            // Get all active groups
            $stmt = $pdo->prepare("SELECT * FROM table_groups WHERE table_id = ? AND status = 'active' ORDER BY started_at");
            $stmt->execute([$tableId]);
            $groups = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get all bills
            $stmt = $pdo->prepare("
                SELECT bs.*, tg.group_name, tg.group_color
                FROM bill_splits bs
                LEFT JOIN table_groups tg ON bs.group_id = tg.group_id
                WHERE bs.table_id = ? AND bs.payment_status != 'voided'
                ORDER BY bs.created_at
            ");
            $stmt->execute([$tableId]);
            $bills = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get items per bill
            foreach ($bills as &$bill) {
                $stmt = $pdo->prepare("SELECT * FROM bill_split_items WHERE bill_id = ? ORDER BY created_at");
                $stmt->execute([$bill['bill_id']]);
                $bill['items'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Calculate totals
            $totalAmount = 0;
            $paidAmount = 0;
            $unpaidAmount = 0;
            foreach ($bills as $bill) {
                $totalAmount += (float)$bill['total'];
                if ($bill['payment_status'] === 'paid') {
                    $paidAmount += (float)$bill['total'];
                } else {
                    $unpaidAmount += (float)$bill['total'];
                }
            }

            return Response::success([
                'table' => $table,
                'groups' => $groups,
                'bills' => $bills,
                'summary' => [
                    'total_groups' => count($groups),
                    'total_bills' => count($bills),
                    'total_amount' => round($totalAmount, 2),
                    'paid_amount' => round($paidAmount, 2),
                    'unpaid_amount' => round($unpaidAmount, 2),
                    'all_paid' => $unpaidAmount === 0,
                ],
            ], 'Table bill summary retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    // === HELPERS ===

    private function recalculateBill($pdo, $billId)
    {
        $stmt = $pdo->prepare("SELECT SUM(total_price) as subtotal FROM bill_split_items WHERE bill_id = ?");
        $stmt->execute([$billId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $subtotal = (float)($result['subtotal'] ?? 0);

        $tax = $subtotal * 0.1; // 10% tax
        $serviceCharge = $subtotal * 0.05; // 5% service charge
        $total = $subtotal + $tax + $serviceCharge;

        $stmt = $pdo->prepare("UPDATE bill_splits SET subtotal = ?, tax = ?, service_charge = ?, total = ?, updated_at = NOW() WHERE bill_id = ?");
        $stmt->execute([$subtotal, $tax, $serviceCharge, $total, $billId]);
    }

    private function logHistory($pdo, $tableId, $action, $description, $userId)
    {
        $stmt = $pdo->prepare("INSERT INTO bill_split_history (table_id, action, description, performed_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tableId, $action, $description, $userId]);
    }
}

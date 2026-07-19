<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleSupplierController
{
    // Simple endpoint to get suppliers without middleware
    public function getSuppliers($request = null)
    {
        $db = db();

        $sql = "SELECT s.supplier_id, s.name AS supplier_name, s.contact_person, s.phone, s.email, s.address, s.is_active AS status
                FROM suppliers s
                WHERE s.deleted_at IS NULL
                ORDER BY s.name ASC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($suppliers);
    }
}

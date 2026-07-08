<?php



class CustomerPricingRepository
{
    private $db;

    public function __construct($db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            $host = 'localhost';
            $dbname = 'ebp_restaurant_db';
            $username = 'ebp_app';
            $password = 'ebp_secure_password_2026';
            $socket = '/opt/lampp/var/mysql/mysql.sock';

            $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
            $this->db = new PDO($dsn, $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function createCustomerPrice($data)
    {
        $sql = "INSERT INTO customer_pricing (tenant_id, branch_id, customer_id, product_id, special_price, discount_percentage, is_complimentary, complimentary_reason, complimentary_code, valid_from, valid_until, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'] ?? null,
            $data['customer_id'],
            $data['product_id'],
            $data['special_price'] ?? null,
            $data['discount_percentage'] ?? null,
            $data['is_complimentary'] ?? false,
            $data['complimentary_reason'] ?? null,
            $data['complimentary_code'] ?? null,
            $data['valid_from'] ?? null,
            $data['valid_until'] ?? null,
            $data['is_active'] ?? true
        ]);
        return $this->db->lastInsertId();
    }

    public function updateCustomerPrice($pricingId, $data)
    {
        $sql = "UPDATE customer_pricing SET special_price = ?, discount_percentage = ?, is_complimentary = ?, complimentary_reason = ?, complimentary_code = ?, valid_from = ?, valid_until = ?, is_active = ? WHERE pricing_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['special_price'] ?? null,
            $data['discount_percentage'] ?? null,
            $data['is_complimentary'] ?? false,
            $data['complimentary_reason'] ?? null,
            $data['complimentary_code'] ?? null,
            $data['valid_from'] ?? null,
            $data['valid_until'] ?? null,
            $data['is_active'] ?? true,
            $pricingId
        ]);
    }

    public function getCustomerProductPrice($tenantId, $branchId, $customerId, $productId)
    {
        $sql = "SELECT * FROM customer_pricing WHERE tenant_id = ? AND (branch_id = ? OR branch_id IS NULL) AND customer_id = ? AND product_id = ? AND is_active = 1 AND (valid_from IS NULL OR valid_from <= CURDATE()) AND (valid_until IS NULL OR valid_until >= CURDATE())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $customerId, $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCustomerPricings($tenantId, $branchId, $customerId)
    {
        $sql = "SELECT cp.*, p.product_name FROM customer_pricing cp JOIN products p ON cp.product_id = p.product_id WHERE cp.tenant_id = ? AND (cp.branch_id = ? OR cp.branch_id IS NULL) AND cp.customer_id = ? AND cp.is_active = 1 ORDER BY cp.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get complimentary items for a customer
     */
    public function getComplimentaryItems($tenantId, $branchId, $customerId)
    {
        $sql = "SELECT cp.*, p.product_name FROM customer_pricing cp JOIN products p ON cp.product_id = p.product_id WHERE cp.tenant_id = ? AND (cp.branch_id = ? OR cp.branch_id IS NULL) AND cp.customer_id = ? AND cp.is_complimentary = 1 AND cp.is_active = 1 AND (valid_from IS NULL OR valid_from <= CURDATE()) AND (valid_until IS NULL OR valid_until >= CURDATE()) ORDER BY cp.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get complimentary report for a tenant
     */
    public function getComplimentaryReport($tenantId, $branchId = null, $dateFrom = null, $dateTo = null)
    {
        $sql = "SELECT cp.*, c.name as customer_name, p.product_name, cp.complimentary_reason, cp.complimentary_code FROM customer_pricing cp JOIN customers c ON cp.customer_id = c.customer_id JOIN products p ON cp.product_id = p.product_id WHERE cp.tenant_id = ? AND cp.is_complimentary = 1";
        $params = [$tenantId];

        if ($branchId) {
            $sql .= " AND (cp.branch_id = ? OR cp.branch_id IS NULL)";
            $params[] = $branchId;
        }

        if ($dateFrom) {
            $sql .= " AND cp.created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo) {
            $sql .= " AND cp.created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }

        $sql .= " ORDER BY cp.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Set item as complimentary for a customer
     */
    public function setComplimentary($tenantId, $branchId, $customerId, $productId, $reason, $code = null)
    {
        $data = [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'product_id' => $productId,
            'special_price' => 0,
            'discount_percentage' => 100,
            'is_complimentary' => true,
            'complimentary_reason' => $reason,
            'complimentary_code' => $code,
            'is_active' => true
        ];
        return $this->createCustomerPrice($data);
    }
}

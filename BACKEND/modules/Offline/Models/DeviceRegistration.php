<?php

namespace App\Modules\Offline\Models;

use App\Core\BaseModel;

class DeviceRegistration extends BaseModel
{
    protected $table = 'device_registrations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'device_id',
        'device_name',
        'device_type',
        'device_os',
        'device_os_version',
        'app_version',
        'storage_capacity_mb',
        'available_storage_mb',
        'is_active',
        'last_seen_at'
    ];

    /**
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND restaurant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $restaurantId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find by device ID
     */
    public function findByDeviceId($deviceId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE device_id = ? AND restaurant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$deviceId, $restaurantId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY last_seen_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
        return $stmt;
    }

    /**
     * Update last seen
     */
    public function updateLastSeen($deviceId, $restaurantId)
    {
        $sql = "UPDATE {$this->table} SET last_seen_at = NOW(), updated_at = NOW() 
                WHERE device_id = ? AND restaurant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$deviceId, $restaurantId]);
        return $stmt;
    }

    /**
     * Get active devices
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = TRUE 
                ORDER BY last_seen_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
        return $stmt;
    }
}

-- Advanced Delivery Tables
-- Phase 2.6: Delivery Optimization

-- Drivers Table
CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    driver_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    vehicle_type ENUM('MOTORCYCLE', 'CAR', 'VAN', 'TRUCK', 'BICYCLE') NOT NULL,
    vehicle_plate VARCHAR(20),
    license_number VARCHAR(50),
    license_expiry DATE,
    status ENUM('ACTIVE', 'INACTIVE', 'ON_LEAVE', 'TERMINATED') DEFAULT 'ACTIVE',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    deleted_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status),
    INDEX idx_vehicle_plate (vehicle_plate),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Delivery Routes Table
CREATE TABLE IF NOT EXISTS delivery_routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    route_name VARCHAR(255) NOT NULL,
    route_date DATE NOT NULL,
    status ENUM('PLANNED', 'ACTIVE', 'COMPLETED', 'CANCELLED') DEFAULT 'PLANNED',
    total_distance_km DECIMAL(10, 2) DEFAULT 0,
    estimated_duration_minutes INT DEFAULT 0,
    actual_duration_minutes INT,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    deleted_at DATETIME,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_route_date (route_date),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Route Stops Table
CREATE TABLE IF NOT EXISTS route_stops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    delivery_order_id INT,
    customer_name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    sequence INT NOT NULL,
    estimated_arrival TIME,
    actual_arrival DATETIME,
    status ENUM('PENDING', 'ARRIVED', 'COMPLETED', 'SKIPPED') DEFAULT 'PENDING',
    notes TEXT,
    created_at DATETIME NOT NULL,
    INDEX idx_route (route_id),
    INDEX idx_delivery_order (delivery_order_id),
    INDEX idx_sequence (sequence),
    INDEX idx_status (status),
    FOREIGN KEY (route_id) REFERENCES delivery_routes(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_order_id) REFERENCES delivery_orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Delivery Tracking Table
CREATE TABLE IF NOT EXISTS delivery_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    delivery_order_id INT NOT NULL,
    driver_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    speed DECIMAL(6, 2) DEFAULT 0,
    heading DECIMAL(5, 2),
    tracking_time DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_delivery (tenant_id, delivery_order_id),
    INDEX idx_driver (driver_id),
    INDEX idx_tracking_time (tracking_time),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_order_id) REFERENCES delivery_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Delivery Notifications Table
CREATE TABLE IF NOT EXISTS delivery_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    delivery_order_id INT NOT NULL,
    notification_type ENUM('STATUS_UPDATE', 'ETA_UPDATE', 'DELIVERY_CONFIRMATION', 'DELAY_ALERT', 'CANCELLATION') NOT NULL,
    recipient_type ENUM('CUSTOMER', 'DRIVER', 'RESTAURANT') NOT NULL,
    recipient_contact VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('PENDING', 'SENT', 'DELIVERED', 'FAILED') DEFAULT 'PENDING',
    sent_at DATETIME,
    delivered_at DATETIME,
    error_message TEXT,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_delivery (tenant_id, delivery_order_id),
    INDEX idx_notification_type (notification_type),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_order_id) REFERENCES delivery_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

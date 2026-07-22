-- Migration: OTP verification table
CREATE TABLE IF NOT EXISTS `otp_verifications` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `tenant_id` bigint(20) unsigned DEFAULT NULL,
    `phone` varchar(20) NOT NULL,
    `email` varchar(255) DEFAULT NULL,
    `otp_code` varchar(6) NOT NULL,
    `purpose` enum('LOGIN','REGISTRATION','PASSWORD_RESET','PHONE_VERIFICATION') DEFAULT 'LOGIN',
    `status` enum('pending','verified','expired','failed') DEFAULT 'pending',
    `attempts` int(11) DEFAULT 0,
    `max_attempts` int(11) DEFAULT 3,
    `expires_at` timestamp NOT NULL DEFAULT (DATE_ADD(NOW(), INTERVAL 5 MINUTE)),
    `verified_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_otp_phone` (`phone`),
    KEY `idx_otp_status` (`status`),
    KEY `idx_otp_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'OTP table created successfully!' AS message;

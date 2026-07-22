-- Migration: Multi-currency tables
CREATE TABLE IF NOT EXISTS `exchange_rates` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `tenant_id` bigint(20) unsigned NOT NULL,
    `currency_code` varchar(3) NOT NULL,
    `exchange_rate` decimal(12,6) NOT NULL,
    `base_currency` varchar(3) DEFAULT 'USD',
    `rate_source` varchar(20) DEFAULT 'MANUAL',
    `effective_date` date NOT NULL,
    `updated_by` bigint(20) unsigned DEFAULT NULL,
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_exchange_rates_tenant_currency_date` (`tenant_id`, `currency_code`, `effective_date`),
    KEY `idx_exchange_rates_tenant_id` (`tenant_id`),
    KEY `idx_exchange_rates_currency_code` (`currency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `active_currencies` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `tenant_id` bigint(20) unsigned NOT NULL,
    `currency_code` varchar(3) NOT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `display_order` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_active_currencies_tenant_currency` (`tenant_id`, `currency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Multi-currency tables created successfully!' AS message;

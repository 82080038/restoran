<?php

declare(strict_types=1);

/**
 * Migration 014: Add i18n/language and feedback/review tables
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getInstance()->connect();

$tables = [
    'languages' => "CREATE TABLE IF NOT EXISTS languages (
        language_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        language_code VARCHAR(10) NOT NULL UNIQUE,
        language_name VARCHAR(50) NOT NULL,
        native_name VARCHAR(50) NOT NULL,
        flag_icon VARCHAR(10) NULL,
        is_active TINYINT(1) DEFAULT 1,
        is_default TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'translations' => "CREATE TABLE IF NOT EXISTS translations (
        translation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        language_id BIGINT NOT NULL,
        translation_key VARCHAR(255) NOT NULL,
        translated_value TEXT NOT NULL,
        context VARCHAR(50) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_lang_key (language_id, translation_key),
        INDEX idx_context (context),
        INDEX idx_language (language_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'user_language_preferences' => "CREATE TABLE IF NOT EXISTS user_language_preferences (
        user_id BIGINT NOT NULL,
        language_id BIGINT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id),
        INDEX idx_language (language_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'reviews' => "CREATE TABLE IF NOT EXISTS reviews (
        review_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        customer_id BIGINT NULL,
        order_id BIGINT NULL,
        rating INT NOT NULL,
        title VARCHAR(200) NULL,
        comment TEXT NOT NULL,
        source VARCHAR(30) DEFAULT 'direct',
        status VARCHAR(20) DEFAULT 'pending',
        is_featured TINYINT(1) DEFAULT 0,
        helpful_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_status (status),
        INDEX idx_rating (rating),
        INDEX idx_source (source)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'review_responses' => "CREATE TABLE IF NOT EXISTS review_responses (
        response_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        review_id BIGINT NOT NULL UNIQUE,
        response_text TEXT NOT NULL,
        responded_by BIGINT NOT NULL,
        responded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_review (review_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'review_categories' => "CREATE TABLE IF NOT EXISTS review_categories (
        category_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NULL,
        category_name VARCHAR(100) NOT NULL,
        category_description VARCHAR(255) NULL,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'review_category_ratings' => "CREATE TABLE IF NOT EXISTS review_category_ratings (
        rating_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        review_id BIGINT NOT NULL,
        category_id BIGINT NOT NULL,
        rating INT NOT NULL,
        UNIQUE KEY uniq_review_category (review_id, category_id),
        INDEX idx_review (review_id),
        INDEX idx_category (category_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'feedback' => "CREATE TABLE IF NOT EXISTS feedback (
        feedback_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        customer_id BIGINT NULL,
        feedback_type VARCHAR(30) NOT NULL DEFAULT 'suggestion',
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        priority VARCHAR(20) DEFAULT 'normal',
        status VARCHAR(20) DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_type (feedback_type),
        INDEX idx_status (status),
        INDEX idx_priority (priority)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'feedback_comments' => "CREATE TABLE IF NOT EXISTS feedback_comments (
        comment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        feedback_id BIGINT NOT NULL,
        user_id BIGINT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_feedback (feedback_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$created = 0;
foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        echo "  + Created/verified table: {$tableName}\n";
        $created++;
    } catch (\PDOException $e) {
        echo "  x Failed: {$tableName}: " . $e->getMessage() . "\n";
    }
}

// Insert default languages
$defaultLanguages = [
    ['en', 'English', 'English', '🇬🇧', 1, 1, 0],
    ['id', 'Indonesian', 'Bahasa Indonesia', '🇮🇩', 1, 0, 1],
    ['zh', 'Chinese', '中文', '🇨🇳', 0, 0, 2],
    ['ja', 'Japanese', '日本語', '🇯🇵', 0, 0, 3],
    ['ko', 'Korean', '한국어', '🇰🇷', 0, 0, 4],
    ['ar', 'Arabic', 'العربية', '🇸🇦', 0, 0, 5],
    ['es', 'Spanish', 'Español', '🇪🇸', 0, 0, 6],
    ['fr', 'French', 'Français', '🇫🇷', 0, 0, 7],
];

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO languages (language_code, language_name, native_name, flag_icon, is_active, is_default, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($defaultLanguages as $lang) {
        $stmt->execute($lang);
    }
    echo "  + Inserted default languages (" . count($defaultLanguages) . ")\n";
} catch (\PDOException $e) {
    echo "  - Skip languages insert: " . $e->getMessage() . "\n";
}

// Insert default review categories
$defaultCategories = [
    ['Food Quality', 'Rate the quality and taste of the food'],
    ['Service', 'Rate the quality of service received'],
    ['Ambiance', 'Rate the restaurant atmosphere and cleanliness'],
    ['Value for Money', 'Rate the value relative to price'],
    ['Wait Time', 'Rate the speed of service and food delivery'],
];

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO review_categories (tenant_id, category_name, category_description, is_active, sort_order) VALUES (NULL, ?, ?, 1, ?)");
    foreach ($defaultCategories as $idx => $cat) {
        $stmt->execute([$cat[0], $cat[1], $idx]);
    }
    echo "  + Inserted default review categories (" . count($defaultCategories) . ")\n";
} catch (\PDOException $e) {
    echo "  - Skip categories insert: " . $e->getMessage() . "\n";
}

// Insert some default English translations
$defaultTranslations = [
    'common.save' => 'Save',
    'common.cancel' => 'Cancel',
    'common.delete' => 'Delete',
    'common.edit' => 'Edit',
    'common.search' => 'Search',
    'common.loading' => 'Loading...',
    'common.error' => 'Error',
    'common.success' => 'Success',
    'common.confirm' => 'Confirm',
    'common.close' => 'Close',
    'common.back' => 'Back',
    'common.next' => 'Next',
    'common.previous' => 'Previous',
    'common.yes' => 'Yes',
    'common.no' => 'No',
    'common.actions' => 'Actions',
    'common.status' => 'Status',
    'common.date' => 'Date',
    'common.time' => 'Time',
    'common.total' => 'Total',
    'common.amount' => 'Amount',
    'common.quantity' => 'Quantity',
    'common.price' => 'Price',
    'common.name' => 'Name',
    'common.description' => 'Description',
    'common.category' => 'Category',
    'common.welcome' => 'Welcome',
    'common.logout' => 'Logout',
    'common.settings' => 'Settings',
    'common.profile' => 'Profile',
    'menu.title' => 'Menu',
    'menu.categories' => 'Categories',
    'menu.products' => 'Products',
    'menu.add_product' => 'Add Product',
    'menu.all_categories' => 'All Categories',
    'orders.title' => 'Orders',
    'orders.order_number' => 'Order Number',
    'orders.status' => 'Order Status',
    'orders.total' => 'Order Total',
    'orders.items' => 'Items',
    'orders.place_order' => 'Place Order',
    'orders.cart' => 'Cart',
    'orders.checkout' => 'Checkout',
    'dashboard.title' => 'Dashboard',
    'dashboard.overview' => 'Overview',
    'dashboard.recent_orders' => 'Recent Orders',
    'dashboard.revenue' => 'Revenue',
    'dashboard.customers' => 'Customers',
    'feedback.title' => 'Feedback',
    'feedback.reviews' => 'Reviews',
    'feedback.rating' => 'Rating',
    'feedback.comment' => 'Comment',
    'feedback.submit' => 'Submit Feedback',
    'feedback.response' => 'Response',
    'feedback.statistics' => 'Statistics',
];

try {
    // Get English language_id
    $stmt = $pdo->prepare("SELECT language_id FROM languages WHERE language_code = 'en'");
    $stmt->execute();
    $langId = $stmt->fetch(\PDO::FETCH_COLUMN);

    if ($langId) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO translations (language_id, translation_key, translated_value, context) VALUES (?, ?, ?, ?)");
        foreach ($defaultTranslations as $key => $value) {
            $context = explode('.', $key)[0];
            $stmt->execute([$langId, $key, $value, $context]);
        }
        echo "  + Inserted default English translations (" . count($defaultTranslations) . ")\n";
    }
} catch (\PDOException $e) {
    echo "  - Skip translations insert: " . $e->getMessage() . "\n";
}

// Insert some default Indonesian translations
$idTranslations = [
    'common.save' => 'Simpan',
    'common.cancel' => 'Batal',
    'common.delete' => 'Hapus',
    'common.edit' => 'Edit',
    'common.search' => 'Cari',
    'common.loading' => 'Memuat...',
    'common.error' => 'Error',
    'common.success' => 'Berhasil',
    'common.confirm' => 'Konfirmasi',
    'common.close' => 'Tutup',
    'common.back' => 'Kembali',
    'common.next' => 'Berikutnya',
    'common.previous' => 'Sebelumnya',
    'common.yes' => 'Ya',
    'common.no' => 'Tidak',
    'common.actions' => 'Aksi',
    'common.status' => 'Status',
    'common.date' => 'Tanggal',
    'common.time' => 'Waktu',
    'common.total' => 'Total',
    'common.amount' => 'Jumlah',
    'common.quantity' => 'Kuantitas',
    'common.price' => 'Harga',
    'common.name' => 'Nama',
    'common.description' => 'Deskripsi',
    'common.category' => 'Kategori',
    'common.welcome' => 'Selamat Datang',
    'common.logout' => 'Keluar',
    'common.settings' => 'Pengaturan',
    'common.profile' => 'Profil',
    'menu.title' => 'Menu',
    'menu.categories' => 'Kategori',
    'menu.products' => 'Produk',
    'menu.add_product' => 'Tambah Produk',
    'menu.all_categories' => 'Semua Kategori',
    'orders.title' => 'Pesanan',
    'orders.order_number' => 'Nomor Pesanan',
    'orders.status' => 'Status Pesanan',
    'orders.total' => 'Total Pesanan',
    'orders.items' => 'Item',
    'orders.place_order' => 'Pesan Sekarang',
    'orders.cart' => 'Keranjang',
    'orders.checkout' => 'Bayar',
    'dashboard.title' => 'Dashboard',
    'dashboard.overview' => 'Ikhtisar',
    'dashboard.recent_orders' => 'Pesanan Terbaru',
    'dashboard.revenue' => 'Pendapatan',
    'dashboard.customers' => 'Pelanggan',
    'feedback.title' => 'Umpan Balik',
    'feedback.reviews' => 'Ulasan',
    'feedback.rating' => 'Penilaian',
    'feedback.comment' => 'Komentar',
    'feedback.submit' => 'Kirim Umpan Balik',
    'feedback.response' => 'Tanggapan',
    'feedback.statistics' => 'Statistik',
];

try {
    $stmt = $pdo->prepare("SELECT language_id FROM languages WHERE language_code = 'id'");
    $stmt->execute();
    $langId = $stmt->fetch(\PDO::FETCH_COLUMN);

    if ($langId) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO translations (language_id, translation_key, translated_value, context) VALUES (?, ?, ?, ?)");
        foreach ($idTranslations as $key => $value) {
            $context = explode('.', $key)[0];
            $stmt->execute([$langId, $key, $value, $context]);
        }
        echo "  + Inserted default Indonesian translations (" . count($idTranslations) . ")\n";
    }
} catch (\PDOException $e) {
    echo "  - Skip ID translations insert: " . $e->getMessage() . "\n";
}

echo "\nMigration 014 complete. Tables: {$created}\n";

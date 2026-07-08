# Analisis dan Kurasi untuk Produk Made-to-Order

## Ringkasan Kebutuhan Bisnis

Produk yang dibuat setelah ada permintaan pelanggan (made-to-order) dengan karakteristik:
1. **Minuman sederhana** (teh manis, kopi) - dibuat setelah pesanan
2. **Produk berbasis berat** (ikan bakar, babi panggang) - harga berbeda per unit karena berat berbeda
3. **Tracking bahan dasar** - ketersediaan bahan mentah (babi panggang mentah, ikan mujahir mentah)
4. **Bumbu/side dish** - biaya dan ketersediaan bumbu saat penyajian

## Analisis Struktur Database Saat Ini

### Fitur yang Sudah Tersedia

#### 1. Product Variants (`product_variants`)
```sql
- variant_id, product_id, variant_code, variant_name
- price_adjustment (penyesuaian harga)
- is_default, status
```
**Kegunaan**: Bisa digunakan untuk variasi ukuran/berat tetap (misal: ikan kecil, sedang, besar)

#### 2. Product Modifiers (`product_modifiers`)
```sql
- modifier_id, modifier_group_id, modifier_code, modifier_name
- price_adjustment (biaya tambahan)
- is_available, status
```
**Kegunaan**: Bisa digunakan untuk bumbu tambahan, side dish

#### 3. Order Item Modifiers (`order_item_modifiers`)
```sql
- order_item_modifier_id, order_item_id, modifier_id
- quantity, unit_price, subtotal
```
**Kegunaan**: Menghubungkan modifier ke item pesanan

#### 4. Inventory System
```sql
- inventory: unit_cost, selling_price, unit (PCS, KG, dll)
- stock_balances: tracking stok per branch
- stock_transactions: tracking pergerakan stok
```
**Kegunaan**: Tracking bahan mentah

### Gap/Kekurangan yang Ditemukan

#### 1. **Harga Dinamis Berdasarkan Berat Aktual**
- **Masalah**: Sistem saat ini hanya support harga tetap atau penyesuaian harga tetap
- **Kebutuhan**: Harga harus dihitung berdasarkan berat aktual saat penjualan
- **Contoh**: Ikan bakar - ikan A 500g @ Rp 50.000/kg = Rp 25.000, ikan B 700g @ Rp 50.000/kg = Rp 35.000

#### 2. **Tracking Individual Inventory Items**
- **Masalah**: Tidak ada tracking per unit individual (batch/lot tracking)
- **Kebutuhan**: Perlu tracking ikan/babi individual dengan berat spesifik
- **Contoh**: Ikan mujahir #001 - 600g, Ikan mujahir #002 - 750g

#### 3. **Link Order Item ke Inventory Item Spesifik**
- **Masalah**: Order item hanya link ke product, tidak ke inventory item spesifik
- **Kebutuhan**: Saat order ikan bakar, harus tracking ikan mana yang digunakan
- **Contoh**: Order ikan bakar menggunakan Ikan mujahir #001

#### 4. **Recipe Cost Calculation untuk Made-to-Order**
- **Masalah**: Recipe system saat ini untuk produksi massal, bukan per-unit
- **Kebutuhan**: Perlu calculation cost per unit aktual berdasarkan bumbu yang digunakan

## Solusi yang Direkomendasikan

### Solusi 1: Weight-Based Pricing (Harga Berbasis Berat)

#### Opsi A: Menggunakan Product Variants dengan Harga per Satuan
```sql
-- Tambah field ke products
ALTER TABLE products ADD COLUMN pricing_type ENUM('FIXED', 'WEIGHT_BASED', 'UNIT_BASED') DEFAULT 'FIXED';
ALTER TABLE products ADD COLUMN unit_price_per_kg DECIMAL(18,2);
ALTER TABLE products ADD COLUMN unit_price_per_unit DECIMAL(18,2);

-- Tambah field ke order_items
ALTER TABLE order_items ADD COLUMN actual_weight DECIMAL(10,3);
ALTER TABLE order_items ADD COLUMN actual_unit_id BIGINT;
ALTER TABLE order_items ADD COLUMN calculated_price DECIMAL(18,2);
```

**Implementasi**:
- Product "Ikan Bakar" dengan `pricing_type = 'WEIGHT_BASED'` dan `unit_price_per_kg = 50000`
- Saat order, input berat aktual: 0.6 kg
- Harga dihitung: 0.6 * 50000 = 30000

#### Opsi B: Inventory Item dengan Weight Tracking
```sql
-- Buat tabel baru untuk tracking individual items
CREATE TABLE inventory_items (
    item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    inventory_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    item_code VARCHAR(50) UNIQUE,
    weight DECIMAL(10,3) NOT NULL,
    unit_cost DECIMAL(18,2),
    calculated_cost DECIMAL(18,2),
    status ENUM('AVAILABLE', 'RESERVED', 'SOLD', 'DISCARDED') DEFAULT 'AVAILABLE',
    received_date DATE,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_inventory (inventory_id),
    INDEX idx_branch (branch_id),
    INDEX idx_status (status)
);
```

**Implementasi**:
- Setiap ikan/babi memiliki record individual dengan weight spesifik
- Saat order, pilih item spesifik yang tersedia
- Harga dihitung otomatis berdasarkan weight

### Solusi 2: Seasoning/Spice Tracking (Tracking Bumbu)

#### Menggunakan Product Modifiers yang Sudah Ada
```sql
-- Setup modifier groups untuk bumbu
INSERT INTO product_modifier_groups (modifier_group_name, description) 
VALUES ('Bumbu Ikan Bakar', 'Pilihan bumbu untuk ikan bakar');

-- Setup modifiers untuk bumbu
INSERT INTO product_modifiers (modifier_group_id, modifier_code, modifier_name, price_adjustment) 
VALUES (1, 'BUMBU_KECAP', 'Bumbu Kecap', 5000),
       (1, 'BUMBU_PADANG', 'Bumbu Padang', 7000),
       (1, 'BUMBU_JIMBARAN', 'Bumbu Jimbaran', 8000);

-- Link ke product
INSERT INTO product_modifier_assignments (product_id, modifier_id) 
VALUES (ikan_bakar_product_id, 1), (ikan_bakar_product_id, 2);
```

**Implementasi**:
- Bumbu di-setup sebagai modifier dengan biaya
- Saat order, pilih bumbu yang diinginkan
- Biaya bumbu ditambahkan ke total harga

#### Inventory Tracking untuk Bumbu
```sql
-- Setup inventory untuk bumbu
INSERT INTO inventory (name, unit, unit_cost, is_perishable) 
VALUES ('Bumbu Kecap', 'LITER', 2000, TRUE),
       ('Bumbu Padang', 'LITER', 3000, TRUE);

-- Recipe untuk bumbu per unit
INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit)
VALUES (bumbu_kecap_recipe_id, bumbu_kecap_inventory_id, 0.05, 'LITER');
```

### Solusi 3: Raw Material Availability Check

#### Inventory Check di Point of Sale
```sql
-- Function untuk check availability
CREATE FUNCTION check_raw_material_availability(product_id BIGINT, branch_id BIGINT) 
RETURNS BOOLEAN
BEGIN
    DECLARE is_available BOOLEAN DEFAULT TRUE;
    
    -- Check recipe ingredients
    SELECT COUNT(*) INTO @missing_count
    FROM recipe_ingredients ri
    JOIN recipes r ON ri.recipe_id = r.recipe_id
    JOIN stock_balances sb ON ri.ingredient_id = sb.inventory_id
    WHERE r.product_id = product_id 
    AND sb.branch_id = branch_id
    AND sb.quantity < ri.quantity;
    
    IF @missing_count > 0 THEN
        SET is_available = FALSE;
    END IF;
    
    RETURN is_available;
END;
```

## Rekomendasi Implementasi

### Prioritas 1: Weight-Based Pricing (Kritis)
1. Tambah field `pricing_type`, `unit_price_per_kg`, `unit_price_per_unit` ke tabel `products`
2. Tambah field `actual_weight`, `calculated_price` ke tabel `order_items`
3. Update POS UI untuk input weight saat order produk weight-based
4. Update calculation logic untuk menghitung harga berdasarkan weight

### Prioritas 2: Individual Item Tracking (Kritis untuk ikan/babi)
1. Buat tabel `inventory_items` untuk tracking individual items
2. Update inventory receiving untuk membuat individual item records
3. Update POS UI untuk memilih item spesifik saat order
4. Update stock deduction untuk mengurangi item spesifik

### Prioritas 3: Seasoning/Spice Tracking (Sedang)
1. Setup product modifiers untuk bumbu yang tersedia
2. Setup inventory untuk bumbu mentah
3. Link modifiers ke inventory untuk cost tracking
4. Update POS UI untuk memilih bumbu saat order

### Prioritas 4: Raw Material Availability Check (Sedang)
1. Implement function untuk check availability
2. Update POS untuk disable produk jika bahan mentah tidak tersedia
3. Add alert system untuk low stock warning

## Contoh Use Case: Ikan Bakar

### Setup Data
```sql
-- Product
INSERT INTO products (name, pricing_type, unit_price_per_kg, unit) 
VALUES ('Ikan Bakar Mujair', 'WEIGHT_BASED', 50000, 'KG');

-- Inventory untuk ikan mentah
INSERT INTO inventory (name, unit, unit_cost) 
VALUES ('Ikan Mujair Mentah', 'KG', 35000);

-- Individual items
INSERT INTO inventory_items (inventory_id, branch_id, item_code, weight, unit_cost) 
VALUES (ikan_mujair_id, branch_id, 'IKM001', 0.6, 21000),
       (ikan_mujair_id, branch_id, 'IKM002', 0.75, 26250);

-- Modifiers untuk bumbu
INSERT INTO product_modifiers (modifier_name, price_adjustment) 
VALUES ('Bumbu Kecap', 5000),
       ('Bumbu Padang', 7000);
```

### Proses Order
1. **Customer pesan Ikan Bakar**
2. **Staff check availability**: Ikan mujair mentah tersedia (2 ekor)
3. **Staff pilih ikan**: IKM001 (0.6 kg)
4. **Staff input ke POS**:
   - Product: Ikan Bakar Mujair
   - Weight: 0.6 kg
   - Calculated price: 0.6 * 50000 = 30000
   - Modifier: Bumbu Kecap (+5000)
   - Total: 35000
5. **System update inventory**:
   - IKM001 status = SOLD
   - Stock ikan mujair mentah -0.6 kg
   - Stock bumbu kecap -0.05 L

## Kesimpulan

Sistem saat ini sudah memiliki fondasi yang baik (product variants, modifiers, inventory) tetapi memerlukan enhancement untuk mendukung model bisnis made-to-order dengan harga dinamis berbasis berat.

Rekomendasi utama:
1. Implement weight-based pricing dengan tracking weight aktual
2. Implement individual item tracking untuk produk dengan variasi berat
3. Leverage existing modifier system untuk bumbu/side dish
4. Implement availability check untuk bahan mentah

Enhancement ini akan memungkinkan sistem mendukung berbagai model bisnis made-to-order dengan akurasi pricing dan inventory tracking yang baik.

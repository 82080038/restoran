# Made-to-Order Implementation Guide

## Overview
This document provides implementation guide for the made-to-order product features, including weight-based pricing, individual item tracking, and modifier selection.

## Backend Implementation

### Database Migrations Completed

#### 1. Weight-Based Pricing (Migration 032)
Added fields to support dynamic pricing:
- `products.pricing_type` - ENUM('FIXED', 'WEIGHT_BASED', 'UNIT_BASED')
- `products.unit_price_per_kg` - Price per kilogram for weight-based products
- `products.unit_price_per_unit` - Price per unit for unit-based products
- `order_items.actual_weight` - Actual weight of the ordered item
- `order_items.actual_unit_id` - Reference to specific inventory item
- `order_items.calculated_price` - Final calculated price

#### 2. Inventory Items Table (Migration 033)
Created `inventory_items` table for tracking individual items:
- `item_id` - Unique identifier
- `inventory_id` - Reference to inventory item type
- `branch_id` - Branch location
- `item_code` - Unique code for the item (e.g., IKM001)
- `weight` - Actual weight in kg
- `unit_cost` - Cost per unit
- `calculated_cost` - Total cost based on weight
- `status` - ENUM('AVAILABLE', 'RESERVED', 'SOLD', 'DISCARDED')
- `received_date` - Date when item was received
- `expiry_date` - Expiry date for perishable items

#### 3. Availability Check (Migration 034)
Placeholder for availability check logic (implemented in application layer due to MariaDB version compatibility).

### API Endpoints Required

The following API endpoints need to be implemented in the backend:

#### Product Endpoints
```php
// Get product with pricing details
GET /api/products/{id}
Response: {
    product_id: 1,
    product_name: "Ikan Bakar Mujair",
    pricing_type: "WEIGHT_BASED",
    unit_price_per_kg: 50000,
    // ... other product fields
}

// Get available modifiers for a product
GET /api/products/{id}/modifiers
Response: {
    groups: [
        {
            modifier_group_id: 1,
            modifier_group_name: "Bumbu Ikan Bakar",
            description: "Pilihan bumbu untuk ikan bakar"
        }
    ],
    modifiers: [
        {
            modifier_id: 1,
            modifier_group_id: 1,
            modifier_name: "Bumbu Kecap",
            price_adjustment: 5000,
            is_available: true
        }
    ]
}
```

#### Inventory Endpoints
```php
// Get available inventory items for a product
GET /api/inventory/available-items/{product_id}/{branch_id}
Response: {
    items: [
        {
            item_id: 1,
            inventory_id: 10,
            item_code: "IKM001",
            weight: 0.6,
            unit_cost: 21000,
            status: "AVAILABLE",
            received_date: "2026-07-08",
            expiry_date: "2026-07-15"
        }
    ]
}

// Get available items by inventory type
GET /api/inventory/items/available/{inventory_id}/{branch_id}
Response: {
    items: [...]
}

// Reserve inventory item
POST /api/inventory/items/{item_id}/reserve
Body: { order_id: 123 }

// Mark inventory item as sold
POST /api/inventory/items/{item_id}/sell
Body: { order_id: 123 }
```

#### Order Endpoints
```php
// Create order with weight-based items
POST /api/orders
Body: {
    branch_id: 1,
    table_id: 5,
    items: [
        {
            product_id: 10,
            quantity: 1,
            actual_weight: 0.6,
            actual_unit_id: 1,
            calculated_price: 30000,
            modifiers: [
                {
                    modifier_id: 1,
                    quantity: 1
                }
            ]
        }
    ]
}
```

## Frontend Implementation

### Component Files Created

#### 1. Weight-Based Ordering Component
**File:** `FRONTEND/js/weight-based-ordering.js`
**Styles:** `FRONTEND/css/weight-based-ordering.css`

**Usage:**
```javascript
// Initialize for a weight-based product
const product = {
    product_id: 10,
    product_name: "Ikan Bakar Mujair",
    pricing_type: "WEIGHT_BASED",
    unit_price_per_kg: 50000
};

await weightBasedOrdering.initialize(product);

// Listen for order addition
document.addEventListener('weightBasedOrderAdded', (e) => {
    const orderItem = e.detail;
    console.log('Added to order:', orderItem);
    // Add to your order management system
});
```

**Features:**
- Weight input with preset buttons (0.3kg, 0.5kg, 0.7kg, 1.0kg)
- Real-time price calculation
- Available items count display
- Unit-based selection for individual items

#### 2. Inventory Item Selector Component
**File:** `FRONTEND/js/inventory-item-selector.js`
**Styles:** `FRONTEND/css/inventory-item-selector.css`

**Usage:**
```javascript
// Initialize for inventory item selection
await inventoryItemSelector.initialize(inventoryId, branchId);

// Listen for selection
document.addEventListener('inventoryItemsSelected', (e) => {
    const { items, selectedIds } = e.detail;
    console.log('Selected items:', items);
    // Process selected items
});
```

**Features:**
- Grid display of available items
- Search by item code
- Filter by status
- Multi-selection support
- Expiry date warning
- Selected items summary

#### 3. Modifier Selector Component
**File:** `FRONTEND/js/modifier-selector.js`
**Styles:** `FRONTEND/css/modifier-selector.css`

**Usage:**
```javascript
// Initialize for product modifiers
await modifierSelector.initialize(productId);

// Listen for selection
document.addEventListener('modifiersSelected', (e) => {
    const { modifiers, selectedIds, totalAdjustment } = e.detail;
    console.log('Selected modifiers:', modifiers);
    console.log('Total adjustment:', totalAdjustment);
    // Add to order item
});

// Quick add (inline usage)
modifierSelector.quickAddModifier(modifierData);
modifierSelector.quickRemoveModifier(modifierId);
```

**Features:**
- Grouped modifiers display
- Checkbox selection
- Price adjustment display
- Selected modifiers summary
- Total price calculation

### Integration with Existing Pages

#### Kiosk Page Integration
Add to `FRONTEND/kiosk/index.html`:

```html
<!-- Add CSS -->
<link rel="stylesheet" href="../css/weight-based-ordering.css">
<link rel="stylesheet" href="../css/inventory-item-selector.css">
<link rel="stylesheet" href="../css/modifier-selector.css">

<!-- Add JS -->
<script src="../js/weight-based-ordering.js"></script>
<script src="../js/inventory-item-selector.js"></script>
<script src="../js/modifier-selector.js"></script>
```

Update `FRONTEND/js/kiosk.js`:

```javascript
// In product click handler
async function handleProductClick(product) {
    if (product.pricing_type === 'WEIGHT_BASED' || product.pricing_type === 'UNIT_BASED') {
        await weightBasedOrdering.initialize(product);
    } else {
        // Standard ordering
        showStandardProductModal(product);
    }
}

// Listen for weight-based orders
document.addEventListener('weightBasedOrderAdded', (e) => {
    addToOrder(e.detail);
});

// Add modifier selection to product modal
function showProductModal(product) {
    // ... existing code ...
    
    // Add modifier button
    const modifierBtn = document.createElement('button');
    modifierBtn.textContent = 'Add Modifiers';
    modifierBtn.className = 'btn btn-secondary';
    modifierBtn.addEventListener('click', () => {
        modifierSelector.initialize(product.product_id);
    });
    
    modal.querySelector('.product-info').appendChild(modifierBtn);
}

// Listen for modifier selection
document.addEventListener('modifiersSelected', (e) => {
    currentOrderItem.modifiers = e.detail.modifiers;
    currentOrderItem.modifierTotal = e.detail.totalAdjustment;
    updateOrderDisplay();
});
```

#### Dashboard POS Integration
Add to `FRONTEND/dashboard/index.html`:

```html
<!-- Add CSS -->
<link rel="stylesheet" href="../css/weight-based-ordering.css">
<link rel="stylesheet" href="../css/inventory-item-selector.css">
<link rel="stylesheet" href="../css/modifier-selector.css">

<!-- Add JS -->
<script src="../js/weight-based-ordering.js"></script>
<script src="../js/inventory-item-selector.js"></script>
<script src="../js/modifier-selector.js"></script>
```

Update order creation flow in dashboard to handle weight-based items.

## Data Setup Examples

### Product Setup
```sql
-- Create weight-based product
INSERT INTO products (product_name, pricing_type, unit_price_per_kg, price, status)
VALUES ('Ikan Bakar Mujair', 'WEIGHT_BASED', 50000, 0, 'ACTIVE');

-- Create unit-based product
INSERT INTO products (product_name, pricing_type, unit_price_per_unit, price, status)
VALUES ('Babi Panggang', 'UNIT_BASED', 60000, 0, 'ACTIVE');
```

### Inventory Setup
```sql
-- Create inventory item type
INSERT INTO inventory (name, unit, unit_cost, status)
VALUES ('Ikan Mujair Mentah', 'KG', 35000, 'ACTIVE');

-- Create individual inventory items
INSERT INTO inventory_items (inventory_id, branch_id, item_code, weight, unit_cost, status, received_date)
VALUES 
(1, 1, 'IKM001', 0.6, 21000, 'AVAILABLE', CURDATE()),
(1, 1, 'IKM002', 0.75, 26250, 'AVAILABLE', CURDATE()),
(1, 1, 'IKM003', 0.5, 17500, 'AVAILABLE', CURDATE());
```

### Modifier Setup
```sql
-- Create modifier group
INSERT INTO product_modifier_groups (modifier_group_name, description)
VALUES ('Bumbu Ikan Bakar', 'Pilihan bumbu untuk ikan bakar');

-- Create modifiers
INSERT INTO product_modifiers (modifier_group_id, modifier_code, modifier_name, price_adjustment, is_available)
VALUES 
(1, 'BUMBU_KECAP', 'Bumbu Kecap', 5000, 1),
(1, 'BUMBU_PADANG', 'Bumbu Padang', 7000, 1),
(1, 'BUMBU_JIMBARAN', 'Bumbu Jimbaran', 8000, 1);

-- Link modifiers to product
INSERT INTO product_modifier_assignments (product_id, modifier_id)
VALUES 
(ikan_bakar_product_id, 1),
(ikan_bakar_product_id, 2),
(ikan_bakar_product_id, 3);
```

## Testing Checklist

### Backend Testing
- [ ] Test weight-based pricing calculation
- [ ] Test unit-based item selection
- [ ] Test inventory item reservation
- [ ] Test inventory item status updates
- [ ] Test modifier price adjustments
- [ ] Test order creation with weight-based items
- [ ] Test stock deduction for individual items

### Frontend Testing
- [ ] Test weight input modal
- [ ] Test weight preset buttons
- [ ] Test price calculation display
- [ ] Test unit selection modal
- [ ] Test item search and filter
- [ ] Test multi-item selection
- [ ] Test modifier selection
- [ ] Test modifier price display
- [ ] Test order integration
- [ ] Test responsive design on mobile

## Common Use Cases

### Use Case 1: Ikan Bakar Order
1. Customer orders "Ikan Bakar Mujair"
2. System detects `pricing_type = 'WEIGHT_BASED'`
3. Staff enters weight: 0.6 kg
4. System calculates price: 0.6 × 50,000 = 30,000
5. Staff selects bumbu: "Bumbu Kecap" (+5,000)
6. Total: 35,000
7. System reserves item IKM001 (0.6 kg)
8. Order created with actual_weight and calculated_price

### Use Case 2: Babi Panggang Order
1. Customer orders "Babi Panggang"
2. System detects `pricing_type = 'UNIT_BASED'`
3. System shows available items with weights
4. Staff selects: BPG001 (1.2 kg)
5. System calculates price: 1.2 × 60,000 = 72,000
6. Staff selects bumbu: "Bumbu Padang" (+7,000)
7. Total: 79,000
8. System reserves item BPG001
9. Order created with actual_unit_id and calculated_price

### Use Case 3: Standard Drink Order
1. Customer orders "Teh Manis"
2. System detects `pricing_type = 'FIXED'`
3. Standard ordering flow
4. No weight/unit selection needed
5. Optional: Add modifier (extra sugar)

## Troubleshooting

### Issue: Weight input not calculating price
**Solution:** Ensure `unit_price_per_kg` is set for the product and the weight input event listener is properly attached.

### Issue: No items available for selection
**Solution:** Check that inventory items exist with status 'AVAILABLE' and are linked to the correct inventory_id and branch_id.

### Issue: Modifiers not showing
**Solution:** Ensure modifiers are created, linked to modifier groups, and assigned to the product via `product_modifier_assignments`.

### Issue: Stock not deducting correctly
**Solution:** Verify that the backend API properly updates inventory item status from 'AVAILABLE' to 'SOLD' and deducts from stock_balances.

## Future Enhancements

1. **Barcode Scanner Integration** - Scan inventory item barcodes for quick selection
2. **Weight Scale Integration** - Connect to digital scales for automatic weight input
3. **Recipe Cost Calculation** - Calculate actual cost based on bumbu usage
4. **Inventory Expiry Alerts** - Proactive alerts for items nearing expiry
5. **Bulk Item Management** - Bulk receive and manage inventory items
6. **Advanced Filtering** - Filter by weight range, received date, etc.
7. **Item Images** - Add images to inventory items for visual identification
8. **Mobile App Integration** - Extend components to mobile app

## Support

For issues or questions regarding the made-to-order implementation:
1. Check this documentation
2. Review the component source code
3. Test with the provided examples
4. Contact development team

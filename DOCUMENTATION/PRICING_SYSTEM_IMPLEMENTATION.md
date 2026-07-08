# Pricing System Implementation Guide

## Overview
This document describes the implementation of the advanced pricing system for handling complex pricing scenarios including combo pricing and weight-based pricing.

## Problem Statement
The restaurant ERP needs to handle complex pricing scenarios:
- **Combo Pricing**: Daging panggang (Rp 30.000) + Nasi (Rp 10.000) = Rp 35.000 (not Rp 40.000)
- **Weight-Based Pricing**: Daging panggang per kilo Rp 250.000
- **Mixed Pricing**: Different prices for different configurations

## Database Changes

### Migration Files
1. **032_add_weight_based_pricing.php** - Adds weight-based pricing fields to products and order_items
2. **033_create_inventory_items_table.php** - Creates individual item tracking for weight-based products
3. **035_create_menu_combos_table.php** - Creates combo pricing tables

### New Tables
- `menu_combos` - Stores combo meal information
- `menu_combo_items` - Stores items included in combos
- `inventory_items` - Stores individual inventory items with specific weights

### Updated Tables
- `products` - Added pricing_type, unit_price_per_kg, unit_price_per_unit fields
- `order_items` - Added combo_id, actual_weight, actual_unit_id, calculated_price fields

## Backend Implementation

### New Controllers
1. **ComboController.php** - Handles combo CRUD operations
   - `create()` - Create new combo
   - `getAll()` - Get all combos
   - `get()` - Get specific combo
   - `update()` - Update combo
   - `delete()` - Delete combo
   - `calculatePrice()` - Calculate combo price

2. **WeightBasedPricingController.php** - Handles weight-based pricing
   - `calculatePrice()` - Calculate price based on weight
   - `getInventoryItems()` - Get available inventory items
   - `getPricingConfig()` - Get product pricing configuration
   - `updatePricingConfig()` - Update product pricing configuration
   - `reserveItem()` - Reserve inventory item
   - `markAsSold()` - Mark item as sold

### New Services
1. **ComboService.php** - Business logic for combo operations
2. **WeightBasedPricingService.php** - Business logic for weight-based pricing

### Updated Services
1. **OrderService.php** - Updated to handle combo and weight-based pricing
2. **OrderRepository.php** - Updated to save pricing fields

### API Routes
```
POST   /api/v1/sales/combos
GET    /api/v1/sales/combos
GET    /api/v1/sales/combos/{id}
PUT    /api/v1/sales/combos/{id}
DELETE /api/v1/sales/combos/{id}
POST   /api/v1/sales/combos/calculate-price

POST   /api/v1/sales/weight-based/calculate-price
GET    /api/v1/sales/weight-based/inventory-items
GET    /api/v1/sales/weight-based/pricing-config
PUT    /api/v1/sales/weight-based/pricing-config
POST   /api/v1/sales/weight-based/reserve-item
POST   /api/v1/sales/weight-based/mark-as-sold
```

## Frontend Implementation

### New JavaScript Components
1. **combo-ordering.js** - Handles combo selection and ordering
2. **weight-based-ordering.js** - Handles weight-based ordering (already existed)

### Integration Points
1. **Product Selection** - Check product pricing_type before adding to cart
2. **Cart Management** - Handle combo and weight-based items in cart
3. **Order Submission** - Include pricing fields in order data

## Usage Examples

### Example 1: Combo Pricing
```javascript
// Create combo
const comboData = {
    combo_code: 'DAGING_NASI',
    combo_name: 'Daging Panggang + Nasi',
    description: 'Paket hemat daging panggang dengan nasi',
    combo_price: 35000,
    discount_amount: 5000,
    items: [
        { menu_id: 1, quantity: 1 }, // Daging panggang
        { menu_id: 2, quantity: 1 }  // Nasi
    ]
};

// Calculate combo price
const priceResult = await comboOrdering.calculateComboPrice(comboData);
// Returns: regular_price: 40000, combo_price: 35000, discount: 5000
```

### Example 2: Weight-Based Pricing
```javascript
// Configure product for weight-based pricing
const pricingConfig = {
    pricing_type: 'WEIGHT_BASED',
    unit_price_per_kg: 250000,
    unit: 'KG'
};

// Calculate price for 0.8 kg
const weightResult = await weightBasedOrdering.calculatePrice({
    product_id: 1,
    actual_weight: 0.8
});
// Returns: calculated_price: 200000 (0.8 * 250000)
```

### Example 3: Order with Mixed Pricing
```javascript
const orderData = {
    items: [
        // Regular item
        {
            product_id: 3,
            qty: 1,
            price: 10000
        },
        // Combo item
        {
            combo_id: 1,
            quantities: { 1: 1, 2: 1 },
            qty: 1,
            price: 35000 // Will be calculated by backend
        },
        // Weight-based item
        {
            product_id: 4,
            qty: 1,
            actual_weight: 0.6,
            price: 150000 // Will be calculated by backend (0.6 * 250000)
        }
    ]
};
```

## Testing Checklist

### Backend Testing
- [ ] Test combo creation and retrieval
- [ ] Test combo price calculation
- [ ] Test weight-based price calculation
- [ ] Test inventory item tracking
- [ ] Test order creation with mixed pricing types
- [ ] Test order repository saves pricing fields

### Frontend Testing
- [ ] Test combo selection modal
- [ ] Test weight input modal
- [ ] Test cart display for combo items
- [ ] Test cart display for weight-based items
- [ ] Test order submission with pricing data
- [ ] Test price calculation in cart

### Integration Testing
- [ ] Test end-to-end combo ordering flow
- [ ] Test end-to-end weight-based ordering flow
- [ ] Test mixed pricing in single order
- [ ] Test inventory deduction for weight-based items
- [ ] Test discount application for combos

## Database Migration Steps

1. Run migration 032: `php MigrationRunner.php up 032`
2. Run migration 033: `php MigrationRunner.php up 033`
3. Run migration 034: `php MigrationRunner.php up 034`
4. Run migration 035: `php MigrationRunner.php up 035`

## Frontend Integration Steps

1. Include the new JavaScript files in your POS HTML:
```html
<script src="js/combo-ordering.js"></script>
<script src="js/weight-based-ordering.js"></script>
```

2. Add event listeners for custom events:
```javascript
document.addEventListener('comboOrderAdded', (e) => {
    // Handle combo added to cart
    const comboItem = e.detail;
    addToCart(comboItem);
});

document.addEventListener('weightBasedOrderAdded', (e) => {
    // Handle weight-based item added to cart
    const weightItem = e.detail;
    addToCart(weightItem);
});
```

3. Update product click handler to check pricing type:
```javascript
function handleProductClick(product) {
    if (product.pricing_type === 'WEIGHT_BASED' || product.pricing_type === 'UNIT_BASED') {
        weightBasedOrdering.initialize(product);
    } else {
        // Regular product handling
        addToCart(product);
    }
}
```

## Troubleshooting

### Common Issues

1. **Migration fails**: Check if tables already exist, use proper rollback
2. **Price calculation incorrect**: Verify unit_price_per_kg is set correctly
3. **Combo not applying discount**: Check combo_price is less than regular price
4. **Inventory items not available**: Ensure inventory_items table is populated
5. **Order submission fails**: Verify all required fields are included

### Debug Mode
Enable debug logging in services by setting:
```php
define('DEBUG_PRICING', true);
```

## Performance Considerations

- Cache combo pricing calculations
- Pre-load inventory items for weight-based products
- Use database indexes on pricing fields
- Implement client-side price calculation for immediate feedback

## Security Considerations

- Validate all weight inputs to prevent negative values
- Sanitize combo item quantities
- Implement proper authentication for pricing configuration changes
- Log all pricing modifications for audit trail

## Future Enhancements

1. **Dynamic Pricing**: Time-based or demand-based pricing
2. **Customer-Specific Pricing**: Special prices for loyalty members
3. **Bulk Discounts**: Tiered pricing for large orders
4. **Promotional Pricing**: Limited-time offers and coupons
5. **Multi-Currency Support**: Price conversion for different currencies

## Support and Maintenance

For issues or questions about the pricing system:
1. Check this documentation first
2. Review the implementation files in BACKEND/modules/Sales/
3. Test with the provided examples
4. Contact development team if issues persist

## Version History

- **v1.0.0** (2026-07-08): Initial implementation
  - Combo pricing system
  - Weight-based pricing system
  - Backend API endpoints
  - Frontend JavaScript components
  - Database migrations

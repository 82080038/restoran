<?php


// Upload Module
if (!class_exists('UploadController')) {
    require_once __DIR__ . '/../modules/Upload/Controllers/UploadController.php';
}

// Auth Module
if (!class_exists('AuthController')) {
    require_once __DIR__ . '/../modules/Auth/Controllers/AuthController.php';
}

// Simple Menu Module (for testing without complex middleware)
if (!class_exists('SimpleMenuController')) {
    require_once __DIR__ . '/../modules/Menu/Controllers/SimpleMenuController.php';
}

// Simple Table Module (for testing without complex middleware)
if (!class_exists('SimpleTableController')) {
    require_once __DIR__ . '/../modules/Table/Controllers/SimpleTableController.php';
}

// Simple Order Module (for testing without complex middleware)
if (!class_exists('SimpleOrderController')) {
    require_once __DIR__ . '/../modules/Sales/Controllers/SimpleOrderController.php';
}

// Simple User Module (for testing without complex middleware)
if (!class_exists('SimpleUserController')) {
    require_once __DIR__ . '/../modules/User/Controllers/SimpleUserController.php';
}

// Simple Inventory Module (for testing without complex middleware)
if (!class_exists('SimpleInventoryController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/SimpleInventoryController.php';
}

// Simple Kitchen Module (for testing without complex middleware)
if (!class_exists('SimpleKitchenController')) {
    require_once __DIR__ . '/../modules/Kitchen/Controllers/SimpleKitchenController.php';
}

// Simple Reservation Module (for testing without complex middleware)
if (!class_exists('SimpleReservationController')) {
    require_once __DIR__ . '/../modules/Reservation/Controllers/SimpleReservationController.php';
}

// Simple Customer Module (for testing without complex middleware)
if (!class_exists('SimpleCustomerController')) {
    require_once __DIR__ . '/../modules/CRM/Controllers/SimpleCustomerController.php';
}

// Simple Employee Module (for testing without complex middleware)
if (!class_exists('SimpleEmployeeController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/SimpleEmployeeController.php';
}

// Simple Delivery Module (for testing without complex middleware)
if (!class_exists('SimpleDeliveryController')) {
    require_once __DIR__ . '/../modules/Delivery/Controllers/SimpleDeliveryController.php';
}

// Simple Supplier Module (for testing without complex middleware)
if (!class_exists('SimpleSupplierController')) {
    require_once __DIR__ . '/../modules/SupplyChain/Controllers/SimpleSupplierController.php';
}

// Sales Module
if (!class_exists('OrderController')) {
    require_once __DIR__ . '/../modules/Sales/Controllers/OrderController.php';
}
if (!class_exists('PaymentManagementController')) {
    require_once __DIR__ . '/../modules/Sales/Controllers/PaymentManagementController.php';
}
if (!class_exists('ComboController')) {
    require_once __DIR__ . '/../modules/Sales/Controllers/ComboController.php';
}
if (!class_exists('WeightBasedPricingController')) {
    require_once __DIR__ . '/../modules/Sales/Controllers/WeightBasedPricingController.php';
}

// Menu Module
if (!class_exists('MenuController')) {
    require_once __DIR__ . '/../modules/Menu/Controllers/MenuController.php';
}
if (!class_exists('ProductVariantController')) {
    require_once __DIR__ . '/../modules/Menu/Controllers/ProductVariantController.php';
}
if (!class_exists('ProductModifierController')) {
    require_once __DIR__ . '/../modules/Menu/Controllers/ProductModifierController.php';
}
if (!class_exists('ComboController')) {
    require_once __DIR__ . '/../modules/Menu/Controllers/ComboController.php';
}

// Table Module
if (!class_exists('TableController')) {
    require_once __DIR__ . '/../modules/Table/Controllers/TableController.php';
}

// Reservation Module
if (!class_exists('ReservationController')) {
    require_once __DIR__ . '/../modules/Reservation/Controllers/ReservationController.php';
}

// Inventory Module
if (!class_exists('InventoryController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/InventoryController.php';
}
if (!class_exists('SupplierController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/SupplierController.php';
}
if (!class_exists('StockAdjustmentController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/StockAdjustmentController.php';
}
if (!class_exists('StockOpnameController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/StockOpnameController.php';
}
if (!class_exists('PurchaseOrderController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/PurchaseOrderController.php';
}
if (!class_exists('GoodsReceiptController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/GoodsReceiptController.php';
}

// Kitchen Module
if (!class_exists('KitchenController')) {
    require_once __DIR__ . '/../modules/Kitchen/Controllers/KitchenController.php';
}

// Location Module
if (!class_exists('LocationController')) {
    require_once __DIR__ . '/../modules/Location/Controllers/LocationController.php';
}

// CRM Module
if (!class_exists('CustomerController')) {
    require_once __DIR__ . '/../modules/CRM/Controllers/CustomerController.php';
}

// AI Module
if (!class_exists('AIController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/AIController.php';
}

// Recipe Module
if (!class_exists('RecipeController')) {
    require_once __DIR__ . '/../modules/Recipe/Controllers/RecipeController.php';
}

// Menu Engineering Module
if (!class_exists('MenuEngineeringController')) {
    require_once __DIR__ . '/../modules/MenuEngineering/Controllers/MenuEngineeringController.php';
}

// Food Waste Module
if (!class_exists('FoodWasteController')) {
    require_once __DIR__ . '/../modules/FoodWaste/Controllers/FoodWasteController.php';
}

// Staff Scheduling Module
if (!class_exists('StaffSchedulingController')) {
    require_once __DIR__ . '/../modules/StaffScheduling/Controllers/StaffSchedulingController.php';
}

// Tip Management Module
if (!class_exists('TipManagementController')) {
    require_once __DIR__ . '/../modules/TipManagement/Controllers/TipManagementController.php';
}

// Central Kitchen Module
if (!class_exists('CentralKitchenController')) {
    require_once __DIR__ . '/../modules/CentralKitchen/Controllers/CentralKitchenController.php';
}

// Multi-Branch Module
if (!class_exists('MultiBranchController')) {
    require_once __DIR__ . '/../modules/MultiBranch/Controllers/MultiBranchController.php';
}

// Advanced HR Module
if (!class_exists('AdvancedHRController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/AdvancedHRController.php';
}


// Advanced Marketing Module
if (!class_exists('AdvancedMarketingController')) {
    require_once __DIR__ . '/../modules/Marketing/Controllers/AdvancedMarketingController.php';
}

// Advanced Delivery Module
if (!class_exists('AdvancedDeliveryController')) {
    require_once __DIR__ . '/../modules/Delivery/Controllers/AdvancedDeliveryController.php';
}

// Multi-Currency Module
if (!class_exists('MultiCurrencyController')) {
    require_once __DIR__ . '/../modules/Currency/Controllers/MultiCurrencyController.php';
}

// HACCP Compliance Module
if (!class_exists('HACCPController')) {
    require_once __DIR__ . '/../modules/Compliance/Controllers/HACCPController.php';
}

// Quality Control Module
if (!class_exists('QualityControlController')) {
    require_once __DIR__ . '/../modules/Quality/Controllers/QualityControlController.php';
}

// Advanced Franchise Module
if (!class_exists('AdvancedFranchiseController')) {
    require_once __DIR__ . '/../modules/Franchise/Controllers/AdvancedFranchiseController.php';
}

// API Marketplace Module
if (!class_exists('APIMarketplaceController')) {
    require_once __DIR__ . '/../modules/API/Controllers/APIMarketplaceController.php';
}

// Infrastructure Monitoring Module
if (!class_exists('InfrastructureMonitoringController')) {
    require_once __DIR__ . '/../modules/Infrastructure/Controllers/InfrastructureMonitoringController.php';
}

// Daily Reports Module
if (!class_exists('DailyReportsController')) {
    require_once __DIR__ . '/../modules/DailyReports/Controllers/DailyReportsController.php';
}

// Delivery Module
if (!class_exists('DeliveryController')) {
    require_once __DIR__ . '/../modules/Delivery/Controllers/DeliveryController.php';
}

// HR Module
if (!class_exists('EmployeeController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/EmployeeController.php';
}

// Accounting Module
if (!class_exists('AccountingController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/AccountingController.php';
}

if (!class_exists('GeneralLedgerController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/GeneralLedgerController.php';
}

if (!class_exists('AccountsReceivableController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/AccountsReceivableController.php';
}

if (!class_exists('AccountsPayableController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/AccountsPayableController.php';
}

if (!class_exists('BankReconciliationController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/BankReconciliationController.php';
}

if (!class_exists('FixedAssetsController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/FixedAssetsController.php';
}

if (!class_exists('BudgetController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/BudgetController.php';
}

if (!class_exists('AccountingPeriodController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/AccountingPeriodController.php';
}

// Supply Chain Module
if (!class_exists('SupplyChainController')) {
    require_once __DIR__ . '/../modules/SupplyChain/Controllers/SupplyChainController.php';
}

// Maintenance Module
if (!class_exists('MaintenanceController')) {
    require_once __DIR__ . '/../modules/Maintenance/Controllers/MaintenanceController.php';
}

// Quality Module
if (!class_exists('QualityController')) {
    require_once __DIR__ . '/../modules/Quality/Controllers/QualityController.php';
}

// Sustainability Module
if (!class_exists('SustainabilityController')) {
    require_once __DIR__ . '/../modules/Sustainability/Controllers/SustainabilityController.php';
}

// Integration Module
if (!class_exists('IntegrationController')) {
    require_once __DIR__ . '/../modules/Integration/Controllers/IntegrationController.php';
}

// Offline Sync Module
if (!class_exists('OfflineSyncController')) {
    require_once __DIR__ . '/../modules/Offline/Controllers/OfflineSyncController.php';
}

// Inventory Advanced Module
if (!class_exists('InventoryAdvancedController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/InventoryAdvancedController.php';
}

// Kitchen Performance Module
if (!class_exists('KitchenPerformanceController')) {
    require_once __DIR__ . '/../modules/Kitchen/Controllers/KitchenPerformanceController.php';
}

// Customer Advanced Module
if (!class_exists('CustomerAdvancedController')) {
    require_once __DIR__ . '/../modules/CRM/Controllers/CustomerAdvancedController.php';
}

// Cost Center Module
if (!class_exists('CostCenterController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/CostCenterController.php';
}

// WhatsApp Module
if (!class_exists('WhatsAppController')) {
    require_once __DIR__ . '/../modules/WhatsApp/Controllers/WhatsAppController.php';
}

// Tax Calculation Module
if (!class_exists('TaxCalculationController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/TaxCalculationController.php';
}

// Supply Chain Module
if (!class_exists('PurchasePlanningController')) {
    require_once __DIR__ . '/../modules/SupplyChain/Controllers/PurchasePlanningController.php';
}
if (!class_exists('QualityControlController')) {
    require_once __DIR__ . '/../modules/SupplyChain/Controllers/QualityControlController.php';
}
if (!class_exists('SupplierPerformanceController')) {
    require_once __DIR__ . '/../modules/SupplyChain/Controllers/SupplierPerformanceController.php';
}

// Currency Module
if (!class_exists('CurrencyController')) {
    require_once __DIR__ . '/../modules/Settings/Controllers/CurrencyController.php';
}

// AI Module
if (!class_exists('SmartProcurementController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/SmartProcurementController.php';
}
if (!class_exists('KitchenIntelligenceController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/KitchenIntelligenceController.php';
}
if (!class_exists('AdvancedProcurementController')) {
    require_once __DIR__ . '/../modules/Procurement/Controllers/AdvancedProcurementController.php';
}
if (!class_exists('CustomerIntelligenceController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/CustomerIntelligenceController.php';
}
if (!class_exists('DynamicPricingController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/DynamicPricingController.php';
}
if (!class_exists('WasteReductionController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/WasteReductionController.php';
}

// Maintenance Module
if (!class_exists('PredictiveMaintenanceController')) {
    require_once __DIR__ . '/../modules/Maintenance/Controllers/PredictiveMaintenanceController.php';
}
if (!class_exists('WorkOrderController')) {
    require_once __DIR__ . '/../modules/Maintenance/Controllers/WorkOrderController.php';
}
if (!class_exists('EquipmentHistoryController')) {
    require_once __DIR__ . '/../modules/Maintenance/Controllers/EquipmentHistoryController.php';
}

// Offline Module
if (!class_exists('OfflineStatusController')) {
    require_once __DIR__ . '/../modules/Offline/Controllers/OfflineStatusController.php';
}

// Kiosk Module
if (!class_exists('KioskController')) {
    require_once __DIR__ . '/../modules/Kiosk/Controllers/KioskController.php';
}

// Mobile Module
if (!class_exists('MobileOrderController')) {
    require_once __DIR__ . '/../modules/Mobile/Controllers/MobileOrderController.php';
}

// Consumer Module
if (!class_exists('ConsumerController')) {
    require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';
}

// WhatsApp Ordering Module
if (!class_exists('WhatsAppOrderingController')) {
    require_once __DIR__ . '/../modules/WhatsApp/Controllers/WhatsAppOrderingController.php';
}

// Quality Compliance Module
if (!class_exists('QualityComplianceController')) {
    require_once __DIR__ . '/../modules/Quality/Controllers/QualityComplianceController.php';
}

// User Module
if (!class_exists('UserController')) {
    require_once __DIR__ . '/../modules/User/Controllers/UserController.php';
}

// Settings Module
if (!class_exists('SettingController')) {
    require_once __DIR__ . '/../modules/Settings/Controllers/SettingController.php';
}

// Report Module
if (!class_exists('ReportController')) {
    require_once __DIR__ . '/../modules/Report/Controllers/ReportController.php';
}

// Tenant Module
if (!class_exists('TenantController')) {
    require_once __DIR__ . '/../modules/Tenant/Controllers/TenantController.php';
}

// AI Advanced Module
if (!class_exists('AdvancedAIController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/AdvancedAIController.php';
}

// Enterprise Module
if (!class_exists('EnterpriseController')) {
    require_once __DIR__ . '/../modules/Enterprise/Controllers/EnterpriseController.php';
}

// Credit Module
if (!class_exists('CreditController')) {
    require_once __DIR__ . '/../modules/CRM/Controllers/CreditController.php';
}

// Customer Pricing Module
if (!class_exists('CustomerPricingController')) {
    require_once __DIR__ . '/../modules/CRM/Controllers/CustomerPricingController.php';
}

// Bonus Module
if (!class_exists('BonusController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/BonusController.php';
}

// Tip Module
if (!class_exists('TipController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/TipController.php';
}

// Commission Module
if (!class_exists('CommissionController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/CommissionController.php';
}

// Loyalty Module
if (!class_exists('LoyaltyController')) {
    require_once __DIR__ . '/../modules/Loyalty/Controllers/LoyaltyController.php';
}

// QR Ordering Module
if (!class_exists('QROrderingController')) {
    require_once __DIR__ . '/../modules/QROrdering/Controllers/QROrderingController.php';
}

// Delivery Integration Module
if (!class_exists('DeliveryIntegrationController')) {
    require_once __DIR__ . '/../modules/Integration/Controllers/DeliveryIntegrationController.php';
}

// Simple Payment Module (compatible with current router pattern)
if (!class_exists('SimplePaymentController')) {
    require_once __DIR__ . '/../modules/Payment/Controllers/SimplePaymentController.php';
}

// Free Payment Module (zero-fee: transfer proof, QRIS static, wallet)
if (!class_exists('FreePaymentController')) {
    require_once __DIR__ . '/../modules/Payment/Controllers/FreePaymentController.php';
}

// Notification Module (SSE + REST)
if (!class_exists('NotificationController')) {
    require_once __DIR__ . '/../modules/Notification/Controllers/NotificationController.php';
}

// Create global aliases for namespaced controllers so short names used below work
foreach (get_declared_classes() as $class) {
    $parts = explode('\\', $class);
    $shortName = end($parts);
    if (str_ends_with($shortName, 'Controller') && $class !== $shortName && !class_exists($shortName)) {
        class_alias($class, $shortName, false);
    }
}


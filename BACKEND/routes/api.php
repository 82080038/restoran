<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

// Load controller includes (kept separate until all controllers are fully PSR-4)
require_once __DIR__ . '/controllers.php';

// Initialize dependencies
$db = new Database();
$router = new Router();
$permissionMiddleware = new PermissionMiddleware();
$authMiddleware = new AuthMiddleware();

// Helper function to apply auth and permission middleware
function withAuthAndPermission($handler, $permission, $permissionMiddleware, $authMiddleware) {
    return function($request) use ($handler, $permission, $permissionMiddleware, $authMiddleware) {
        try {
            // Apply auth middleware
            $request = $authMiddleware->handle($request);
            $request = \App\Core\TenantMiddleware::handle($request);
            $request = \App\Core\AuditMiddleware::handle($request);

            // Apply permission middleware
            $userId = $request['user_id'] ?? null;
            $isPlatformOwner = $request['is_platform_owner'] ?? false;
            $isTenantOwner = $request['is_tenant_owner'] ?? false;

            if (!$userId) {
                return Response::unauthorized();
            }

            if (!$permissionMiddleware->check($userId, $permission, $isPlatformOwner, $isTenantOwner)) {
                return Response::error("You don't have permission to access this resource", 403);
            }

            // Call the actual handler
            return $handler($request);
        } catch (\Throwable $e) {
            $statusCode = (int) $e->getCode();
            $statusCode = $statusCode >= 400 && $statusCode < 600 ? $statusCode : 500;
            $message = getenv('APP_DEBUG') === 'true' ? $e->getMessage() : 'Internal server error';
            return Response::error($message, $statusCode);
        }
    };
}

// Helper function to apply auth middleware only
function withAuth($handler, $authMiddleware) {
    return function($request) use ($handler, $authMiddleware) {
        try {
            // Apply auth middleware
            $request = $authMiddleware->handle($request);
            $request = \App\Core\TenantMiddleware::handle($request);
            $request = \App\Core\AuditMiddleware::handle($request);
            
            // Call the actual handler
            return $handler($request);
        } catch (\Throwable $e) {
            $statusCode = (int) $e->getCode();
            $statusCode = $statusCode >= 400 && $statusCode < 600 ? $statusCode : 500;
            $message = getenv('APP_DEBUG') === 'true' ? $e->getMessage() : 'Internal server error';
            return Response::error($message, $statusCode);
        }
    };
}

// Initialize controllers
$simpleMenuController = new LazyController(SimpleMenuController::class);
$simpleOrderController = new LazyController(SimpleOrderController::class);
$simpleUserController = new LazyController(SimpleUserController::class);
$simpleTableController = new LazyController(SimpleTableController::class);
$simpleInventoryController = new LazyController(SimpleInventoryController::class);
$simpleKitchenController = new LazyController(SimpleKitchenController::class);
$simpleReservationController = new LazyController(SimpleReservationController::class);
$simpleCustomerController = new LazyController(SimpleCustomerController::class);
$simpleEmployeeController = new LazyController(SimpleEmployeeController::class);
$simpleDeliveryController = new LazyController(SimpleDeliveryController::class);
$simpleSupplierController = new LazyController(SimpleSupplierController::class);
$authController = new LazyController(AuthController::class);
$orderController = new LazyController(OrderController::class);
$paymentManagementController = new LazyController(PaymentManagementController::class);
$comboController = new LazyController(ComboController::class);
$weightBasedPricingController = new LazyController(WeightBasedPricingController::class);
$menuController = new LazyController(MenuController::class);
$productVariantController = new LazyController(ProductVariantController::class);
$productModifierController = new LazyController(ProductModifierController::class);
$recipeController = new LazyController(RecipeController::class);
$menuEngineeringController = new LazyController(MenuEngineeringController::class);
$foodWasteController = new LazyController(FoodWasteController::class);
$staffSchedulingController = new LazyController(StaffSchedulingController::class);
$tipManagementController = new LazyController(TipManagementController::class);
$dailyReportsController = new LazyController(DailyReportsController::class);
$centralKitchenController = new LazyController(CentralKitchenController::class);
$multiBranchController = new LazyController(MultiBranchController::class);
$advancedHRController = new LazyController(AdvancedHRController::class);
$advancedMarketingController = new LazyController(AdvancedMarketingController::class);
$advancedDeliveryController = new LazyController(AdvancedDeliveryController::class);
$multiCurrencyController = new LazyController(MultiCurrencyController::class);
$haccpController = new LazyController(HACCPController::class);
$qualityControlController = new LazyController(QualityControlController::class);
$advancedFranchiseController = new LazyController(AdvancedFranchiseController::class);
$apiMarketplaceController = new LazyController(APIMarketplaceController::class);
$infrastructureMonitoringController = new LazyController(InfrastructureMonitoringController::class);
$tableController = new LazyController(TableController::class);
$reservationController = new LazyController(ReservationController::class);
$inventoryController = new LazyController(InventoryController::class);
$supplierController = new LazyController(SupplierController::class);
$stockAdjustmentController = new LazyController(StockAdjustmentController::class);
$stockOpnameController = new LazyController(StockOpnameController::class);
$purchaseOrderController = new LazyController(PurchaseOrderController::class);
$goodsReceiptController = new LazyController(GoodsReceiptController::class);
$kitchenController = new LazyController(KitchenController::class);
$locationController = new LazyController(LocationController::class);
$customerController = new LazyController(CustomerController::class);
$uploadController = new LazyController(UploadController::class);
$aiController = new LazyController(AIController::class);
$deliveryController = new LazyController(DeliveryController::class);
$employeeController = new LazyController(EmployeeController::class);
$accountingController = new LazyController(AccountingController::class);
$generalLedgerController = new LazyController(GeneralLedgerController::class);
$accountsReceivableController = new LazyController(AccountsReceivableController::class);
$accountsPayableController = new LazyController(AccountsPayableController::class);
$bankReconciliationController = new LazyController(BankReconciliationController::class);
$fixedAssetsController = new LazyController(FixedAssetsController::class);
$budgetController = new LazyController(BudgetController::class);
$accountingPeriodController = new LazyController(AccountingPeriodController::class);
$supplyChainController = new LazyController(SupplyChainController::class);
$maintenanceController = new LazyController(MaintenanceController::class);
$qualityController = new LazyController(QualityController::class);
$sustainabilityController = new LazyController(SustainabilityController::class);
$integrationController = new LazyController(IntegrationController::class);
$advancedAIController = new LazyController(AdvancedAIController::class);
$enterpriseController = new LazyController(EnterpriseController::class);
$offlineSyncController = new LazyController(OfflineSyncController::class);
$creditController = new LazyController(CreditController::class);
$customerPricingController = new LazyController(CustomerPricingController::class);
$bonusController = new LazyController(BonusController::class);
$tipController = new LazyController(TipController::class);
$commissionController = new LazyController(CommissionController::class);
$inventoryAdvancedController = new LazyController(InventoryAdvancedController::class);
$kitchenPerformanceController = new LazyController(KitchenPerformanceController::class);
$customerAdvancedController = new LazyController(CustomerAdvancedController::class);
$costCenterController = new LazyController(CostCenterController::class);
$whatsappController = new LazyController(WhatsAppController::class);
$simplePaymentController = new LazyController(SimplePaymentController::class);
$freePaymentController = new LazyController(FreePaymentController::class);
$notificationController = new LazyController(NotificationController::class);
$deliveryIntegrationController = new LazyController(DeliveryIntegrationController::class);
$qrOrderingController = new LazyController(QROrderingController::class);
$happyHourController = new LazyController(HappyHourController::class);
$simpleLanguageController = new LazyController(SimpleLanguageController::class);
$simpleFeedbackController = new LazyController(SimpleFeedbackController::class);
$floorPlanController = new LazyController(FloorPlanController::class);
$billSplitController = new LazyController(BillSplitController::class);
$nightclubController = new LazyController(NightclubController::class);
$entertainmentController = new LazyController(\App\Modules\Entertainment\Controllers\EntertainmentController::class);
$posBankReconciliationController = new LazyController(\App\Modules\POSBankReconciliation\Controllers\POSBankReconciliationController::class);
$beverageVarianceController = new LazyController(\App\Modules\BeverageVariance\Controllers\BeverageVarianceController::class);
$recipeDepletionController = new LazyController(\App\Modules\RecipeDepletion\Controllers\RecipeDepletionController::class);
$batchExpiryController = new LazyController(\App\Modules\BatchExpiry\Controllers\BatchExpiryController::class);
$settlementController = new LazyController(\App\Modules\Settlement\Controllers\SettlementController::class);
$eventProfitabilityController = new LazyController(\App\Modules\EventProfitability\Controllers\EventProfitabilityController::class);
$eventProposalController = new LazyController(\App\Modules\EventProposal\Controllers\EventProposalController::class);
$nightclubAdvancedController = new LazyController(\App\Modules\NightclubAdvanced\Controllers\NightclubAdvancedController::class);
$karaokeAdvancedController = new LazyController(\App\Modules\KaraokeAdvanced\Controllers\KaraokeAdvancedController::class);
$beachClubAdvancedController = new LazyController(\App\Modules\BeachClubAdvanced\Controllers\BeachClubAdvancedController::class);
$sportsBarAdvancedController = new LazyController(\App\Modules\SportsBarAdvanced\Controllers\SportsBarAdvancedController::class);
$operationsAdvancedController = new LazyController(\App\Modules\OperationsAdvanced\Controllers\OperationsAdvancedController::class);
$venueAdvancedController = new LazyController(\App\Modules\VenueAdvanced\Controllers\VenueAdvancedController::class);
$tier3OperationsController = new LazyController(\App\Modules\OperationsAdvanced\Controllers\Tier3OperationsController::class);
$miscFeaturesController = new LazyController(\App\Modules\MiscFeatures\Controllers\MiscFeaturesController::class);
$taxCalculationController = new LazyController(TaxCalculationController::class);
$purchasePlanningController = new LazyController(PurchasePlanningController::class);
$supplierPerformanceController = new LazyController(SupplierPerformanceController::class);
$currencyController = new LazyController(CurrencyController::class);
$smartProcurementController = new LazyController(SmartProcurementController::class);
$kitchenIntelligenceController = new LazyController(KitchenIntelligenceController::class);
$advancedProcurementController = new LazyController(AdvancedProcurementController::class);
$customerIntelligenceController = new LazyController(CustomerIntelligenceController::class);
$dynamicPricingController = new LazyController(DynamicPricingController::class);
$wasteReductionController = new LazyController(WasteReductionController::class);
$predictiveMaintenanceController = new LazyController(PredictiveMaintenanceController::class);
$workOrderController = new LazyController(WorkOrderController::class);
$equipmentHistoryController = new LazyController(EquipmentHistoryController::class);
$offlineStatusController = new LazyController(OfflineStatusController::class);
$kioskController = new LazyController(KioskController::class);
$mobileOrderController = new LazyController(MobileOrderController::class);
$consumerController = new LazyController(ConsumerController::class);
$whatsAppOrderingController = new LazyController(WhatsAppOrderingController::class);
$qualityComplianceController = new LazyController(QualityComplianceController::class);
$userController = new LazyController(UserController::class);
$settingController = new LazyController(SettingController::class);
$reportController = new LazyController(ReportController::class);
$tenantController = new LazyController(TenantController::class);
$floorController = new LazyController(\App\Modules\Facility\Controllers\FloorController::class);
$zoneController = new LazyController(\App\Modules\Facility\Controllers\ZoneController::class);
$kitchenStationController = new LazyController(\App\Modules\Facility\Controllers\KitchenStationController::class);
$kdsScreenController = new LazyController(\App\Modules\KDS\Controllers\KDSScreenController::class);
$kdsRoutingRuleController = new LazyController(\App\Modules\KDS\Controllers\KDSRoutingRuleController::class);
$kdsTicketController = new LazyController(\App\Modules\KDS\Controllers\KDSTicketController::class);
$waitlistController = new LazyController(\App\Modules\Waitlist\Controllers\WaitlistController::class);
$peakHourController = new LazyController(\App\Modules\Operations\Controllers\PeakHourController::class);
$courseFiringController = new LazyController(\App\Modules\Operations\Controllers\CourseFiringController::class);
$ayceController = new LazyController(\App\Modules\Operations\Controllers\AYCEController::class);
$loadBalancingController = new LazyController(\App\Modules\Operations\Controllers\LoadBalancingController::class);
$performanceMonitoringController = new LazyController(\App\Modules\Operations\Controllers\PerformanceMonitoringController::class);
// Loyalty controller instantiated per route to avoid dependency issues


// Load per-module route definitions
require_once __DIR__ . '/api/001_Auth_Routes.php';
require_once __DIR__ . '/api/002_Upload_Routes.php';
require_once __DIR__ . '/api/003_Tenant_Routes.php';
require_once __DIR__ . '/api/004_Sales_Routes.php';
require_once __DIR__ . '/api/005_Payment_Management_Routes.php';
require_once __DIR__ . '/api/006_Combo_Pricing_Routes.php';
require_once __DIR__ . '/api/007_Weight_Based_Pricing_Routes.php';
require_once __DIR__ . '/api/008_Menu_Engineering_Routes.php';
require_once __DIR__ . '/api/009_Food_Waste_Routes.php';
require_once __DIR__ . '/api/010_Staff_Scheduling_Routes.php';
require_once __DIR__ . '/api/011_Tip_Management_Routes.php';
require_once __DIR__ . '/api/012_Daily_Reports_Routes.php';
require_once __DIR__ . '/api/013_Central_Kitchen_Routes.php';
require_once __DIR__ . '/api/014_Multi_Branch_Routes.php';
require_once __DIR__ . '/api/015_Advanced_HR_Routes.php';
require_once __DIR__ . '/api/016_Advanced_Marketing_Routes.php';
require_once __DIR__ . '/api/017_Advanced_Delivery_Routes.php';
require_once __DIR__ . '/api/018_Multi_Currency_Routes.php';
require_once __DIR__ . '/api/019_HACCP_Compliance_Routes.php';
require_once __DIR__ . '/api/020_Quality_Control_Routes.php';
require_once __DIR__ . '/api/021_Advanced_Franchise_Routes.php';
require_once __DIR__ . '/api/022_API_Marketplace_Routes.php';
require_once __DIR__ . '/api/023_Infrastructure_Monitoring_Routes.php';
require_once __DIR__ . '/api/024_Table_Routes.php';
require_once __DIR__ . '/api/025_Reservation_Routes.php';
require_once __DIR__ . '/api/026_Location_Routes.php';
require_once __DIR__ . '/api/027_Inventory_Routes.php';
require_once __DIR__ . '/api/028_Supplier_Routes.php';
require_once __DIR__ . '/api/029_Stock_Adjustment_Routes.php';
require_once __DIR__ . '/api/030_Stock_Opname_Routes.php';
require_once __DIR__ . '/api/031_Purchase_Order_Routes.php';
require_once __DIR__ . '/api/032_Goods_Receipt_Routes.php';
require_once __DIR__ . '/api/033_CRM_Routes.php';
require_once __DIR__ . '/api/034_Kitchen_Routes.php';
require_once __DIR__ . '/api/035_User_Routes.php';
require_once __DIR__ . '/api/036_Settings_Routes.php';
require_once __DIR__ . '/api/037_Report_Routes.php';
require_once __DIR__ . '/api/038_AI_Routes.php';
require_once __DIR__ . '/api/039_Delivery_Routes.php';
require_once __DIR__ . '/api/040_HR_Routes.php';
require_once __DIR__ . '/api/041_Accounting_Routes.php';
require_once __DIR__ . '/api/042_General_Ledger_Routes.php';
require_once __DIR__ . '/api/043_Accounts_Receivable_Routes.php';
require_once __DIR__ . '/api/044_Accounts_Payable_Routes.php';
require_once __DIR__ . '/api/045_Bank_Reconciliation_Routes.php';
require_once __DIR__ . '/api/046_Fixed_Assets_Routes.php';
require_once __DIR__ . '/api/047_Budget_Management_Routes.php';
require_once __DIR__ . '/api/048_Accounting_Period_Routes.php';
require_once __DIR__ . '/api/049_Account_Suggestion_Routes.php';
require_once __DIR__ . '/api/050_Currency_Service_Routes.php';
require_once __DIR__ . '/api/051_Report_Queue_Routes.php';
require_once __DIR__ . '/api/052_Supply_Chain_Routes.php';
require_once __DIR__ . '/api/053_Maintenance_Routes.php';
require_once __DIR__ . '/api/054_Quality_Routes.php';
require_once __DIR__ . '/api/055_Sustainability_Routes.php';
require_once __DIR__ . '/api/056_Integration_Routes.php';
require_once __DIR__ . '/api/057_Advanced_AI_Routes.php';
require_once __DIR__ . '/api/058_Enterprise_Routes.php';
require_once __DIR__ . '/api/059_Offline_Sync_Routes.php';
require_once __DIR__ . '/api/060_Customer_Credit_Routes.php';
require_once __DIR__ . '/api/061_Customer_Pricing_Routes.php';
require_once __DIR__ . '/api/062_Bonus_Routes.php';
require_once __DIR__ . '/api/063_Tip_Routes.php';
require_once __DIR__ . '/api/064_Commission_Routes.php';
require_once __DIR__ . '/api/065_Report_Export_Routes.php';
require_once __DIR__ . '/api/066_Inventory_Advanced_Routes.php';
require_once __DIR__ . '/api/067_Kitchen_Performance_Routes.php';
require_once __DIR__ . '/api/068_Customer_Advanced_Routes.php';
require_once __DIR__ . '/api/069_Cost_Center_Routes.php';
require_once __DIR__ . '/api/070_WhatsApp_Routes.php';
require_once __DIR__ . '/api/071_Tax_Calculation_Routes.php';
require_once __DIR__ . '/api/072_Supply_Chain_Routes.php';
require_once __DIR__ . '/api/073_Supplier_Performance_Routes.php';
require_once __DIR__ . '/api/074_Currency_Routes.php';
require_once __DIR__ . '/api/075_AI_Smart_Procurement_Routes.php';
require_once __DIR__ . '/api/076_Advanced_Procurement_Routes.php';
require_once __DIR__ . '/api/077_AI_Kitchen_Intelligence_Routes.php';
require_once __DIR__ . '/api/078_AI_Customer_Intelligence_Routes.php';
require_once __DIR__ . '/api/079_AI_Dynamic_Pricing_Routes.php';
require_once __DIR__ . '/api/080_AI_Waste_Reduction_Routes.php';
require_once __DIR__ . '/api/081_Predictive_Maintenance_Routes.php';
require_once __DIR__ . '/api/082_Work_Order_Routes.php';
require_once __DIR__ . '/api/083_Equipment_History_Routes.php';
require_once __DIR__ . '/api/084_Offline_Status_Routes.php';
require_once __DIR__ . '/api/085_Kiosk_Routes.php';
require_once __DIR__ . '/api/086_Mobile_Routes.php';
require_once __DIR__ . '/api/087_Consumer_Auth_Routes.php';
require_once __DIR__ . '/api/088_WhatsApp_Ordering_Routes.php';
require_once __DIR__ . '/api/089_Quality_Compliance_Routes.php';
require_once __DIR__ . '/api/090_Feature_Toggle_Routes.php';
require_once __DIR__ . '/api/091_Attendance_Routes.php';
require_once __DIR__ . '/api/092_Holiday_Routes.php';
require_once __DIR__ . '/api/093_Business_Hours_Routes.php';
require_once __DIR__ . '/api/094_Emergency_Closure_Routes.php';
require_once __DIR__ . '/api/095_Role_Fallback_Routes.php';
require_once __DIR__ . '/api/096_Menu_Navigation_Routes.php';
require_once __DIR__ . '/api/097_Custom_Role_Routes.php';
require_once __DIR__ . '/api/098_Custom_Module_Routes.php';
require_once __DIR__ . '/api/099_Custom_Permission_Routes.php';
require_once __DIR__ . '/api/125_Payment_Notification_Routes.php';
require_once __DIR__ . '/api/100_Facility_Routes.php';
require_once __DIR__ . '/api/126_Delivery_Integration_Routes.php';
require_once __DIR__ . '/api/101_Advanced_Operations_Routes.php';
require_once __DIR__ . '/api/127_QR_Ordering_Routes.php';
require_once __DIR__ . '/api/102_Free_Payment_Routes.php';
require_once __DIR__ . '/api/103_Happy_Hour_Routes.php';
require_once __DIR__ . '/api/104_Language_Feedback_Routes.php';
require_once __DIR__ . '/api/105_Floor_Plan_Routes.php';
require_once __DIR__ . '/api/106_Bill_Split_Routes.php';
require_once __DIR__ . '/api/107_Nightclub_Routes.php';
require_once __DIR__ . '/api/108_Entertainment_Routes.php';
require_once __DIR__ . '/api/109_POS_Bank_Reconciliation_Routes.php';
require_once __DIR__ . '/api/110_Beverage_Variance_Routes.php';
require_once __DIR__ . '/api/111_Recipe_Depletion_Routes.php';
require_once __DIR__ . '/api/112_Batch_Expiry_Routes.php';
require_once __DIR__ . '/api/113_Settlement_Routes.php';
require_once __DIR__ . '/api/114_Event_Profitability_Routes.php';
require_once __DIR__ . '/api/115_BEO_Event_Proposal_Routes.php';
require_once __DIR__ . '/api/116_Nightclub_Advanced_Routes.php';
require_once __DIR__ . '/api/117_Karaoke_Advanced_Routes.php';
require_once __DIR__ . '/api/118_Beach_Club_Advanced_Routes.php';
require_once __DIR__ . '/api/119_Sports_Bar_Advanced_Routes.php';
require_once __DIR__ . '/api/120_Operations_Advanced_Routes.php';
require_once __DIR__ . '/api/121_Venue_Advanced_Routes.php';
require_once __DIR__ . '/api/122_Tier3_Operations_Routes.php';
require_once __DIR__ . '/api/123_Misc_Features_Routes.php';
require_once __DIR__ . '/api/124_Gap_Features_Routes.php';
require_once __DIR__ . '/api/130_Missing_Routes.php';

// Dispatch the request
$router->dispatch();

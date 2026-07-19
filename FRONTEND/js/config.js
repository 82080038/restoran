/**
 * Food & Beverages Management System Frontend Configuration
 * 
 * This file contains all configuration values for the frontend application.
 * In production, these should be loaded from environment-specific config files.
 */

/**
 * Auto-detect API base URL from current page path.
 * Works for both Apache (e.g. /restoran/api/v1) and PHP dev server (e.g. /api/v1).
 */
function detectApiBaseURL() {
    // If explicitly set via window.API_BASE_URL, use it
    if (window.API_BASE_URL) return window.API_BASE_URL;

    var path = window.location.pathname;
    // Match common base paths like /restoran/, /EBP/, /myapp/ etc.
    // Frontend pages are served from <base>/FRONTEND/ or <base>/consumer/ etc.
    var match = path.match(/^(\/[^/]+)\/(FRONTEND|api|consumer|dashboard|kiosk|mobile|index|login|reset-password|bill-split|floor-plan|floor-status|qr-order)/);
    if (match) {
        return match[1] + '/api/v1';
    }
    // Default: API is at root
    return '/api/v1';
}

const Config = {
    // API Configuration
    api: {
        baseURL: detectApiBaseURL(),
        timeout: 30000, // 30 seconds
        retryAttempts: 3
    },
    
    // Application Configuration
    app: {
        name: 'F&B Management System',
        version: '1.0.0',
        environment: window.APP_ENV || 'development',
        debug: window.APP_DEBUG === 'true' || false
    },
    
    // Authentication Configuration
    auth: {
        tokenKey: 'authToken',
        userKey: 'ebp_user',
        tenantIdKey: 'tenantId',
        branchIdKey: 'branchId',
        tokenRefreshThreshold: 300 // 5 minutes before expiration
    },
    
    // UI Configuration
    ui: {
        itemsPerPage: 20,
        dateFormat: 'DD/MM/YYYY',
        timeFormat: 'HH:mm',
        currency: 'IDR',
        currencySymbol: 'Rp'
    },
    
    // Feature Flags
    features: {
        enableLoyalty: true,
        enableDelivery: true,
        enableReservations: true,
        enableKitchenDisplay: true,
        enableReports: true
    }
};

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Config;
}

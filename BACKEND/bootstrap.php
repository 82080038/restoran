<?php

/**
 * EBP Backend Bootstrap
 * 
 * This file loads all EBP Core components
 * Include this file at the beginning of your application
 */

// Load EBP Core Components (local to BACKEND/core)
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/JWT.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/BaseController.php';
require_once __DIR__ . '/core/BaseRepository.php';
require_once __DIR__ . '/core/BaseService.php';
require_once __DIR__ . '/core/Pagination.php';
require_once __DIR__ . '/core/ValidationException.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/Middleware/AuthMiddleware.php';

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

// Set default values (only used if .env doesn't exist or doesn't have these values)
if (!getenv('DB_HOST')) putenv('DB_HOST=localhost');
if (!getenv('DB_SOCKET')) putenv('DB_SOCKET=');
if (!getenv('DB_NAME')) putenv('DB_NAME=');
if (!getenv('DB_USER')) putenv('DB_USER=');
if (!getenv('DB_PASSWORD')) putenv('DB_PASSWORD=');
if (!getenv('JWT_SECRET')) putenv('JWT_SECRET=');
if (!getenv('JWT_ALGORITHM')) putenv('JWT_ALGORITHM=HS256');
if (!getenv('JWT_EXPIRATION')) putenv('JWT_EXPIRATION=3600');

// Load Composer autoloader (PSR-4 for App\Core\ and App\Modules\)
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Load Backend-specific Components
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Transaction.php';
require_once __DIR__ . '/core/Audit.php';
require_once __DIR__ . '/core/Messages.php';
require_once __DIR__ . '/core/ScreenSizeHelper.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/core/Middleware/PermissionMiddleware.php';
require_once __DIR__ . '/core/Middleware/TenantMiddleware.php';
require_once __DIR__ . '/core/Middleware/ErrorHandler.php';
require_once __DIR__ . '/core/Middleware/ValidationMiddleware.php';
require_once __DIR__ . '/core/Middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/core/Middleware/AuditMiddleware.php';

// Load all Engines
require_once __DIR__ . '/core/Engines/StockEngine.php';
require_once __DIR__ . '/core/Engines/KitchenEngine.php';
require_once __DIR__ . '/core/Engines/AccountingEngine.php';
require_once __DIR__ . '/core/Engines/ReconciliationEngine.php';
require_once __DIR__ . '/core/Engines/ComplianceEngine.php';
require_once __DIR__ . '/core/Engines/PricingEngine.php';
require_once __DIR__ . '/core/Engines/SchedulingEngine.php';
require_once __DIR__ . '/core/Engines/RecipeEngine.php';
require_once __DIR__ . '/core/Engines/LoyaltyEngine.php';
require_once __DIR__ . '/core/Engines/AIEngine.php';
require_once __DIR__ . '/core/Engines/SustainabilityEngine.php';
require_once __DIR__ . '/core/Engines/RiskEngine.php';
require_once __DIR__ . '/core/Engines/AnalyticsEngine.php';

// Load Integration Components
require_once __DIR__ . '/core/Integration/IntegrationConnector.php';

// Create global aliases for App\Core classes so legacy/global references keep working
foreach (get_declared_classes() as $class) {
    if (strpos($class, 'App\\Core\\') !== 0) {
        continue;
    }
    $parts = explode('\\', $class);
    $shortName = end($parts);
    if (!class_exists($shortName, false)) {
        class_alias($class, $shortName, false);
    }
}

/**
 * Lazy controller wrapper.
 * Delays controller construction until a route method is actually called.
 * This avoids eager-loading every controller on every request and prevents
 * a single broken controller from crashing the whole API.
 */
class LazyController
{
    private string $class;
    private ?object $instance = null;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function __call(string $method, array $args)
    {
        if ($this->instance === null) {
            $this->instance = new $this->class();
        }
        return $this->instance->$method(...$args);
    }
}

/**
 * Global database helper.
 * Returns a shared PDO connection from the Database singleton.
 */
function db(): PDO
{
    return \App\Core\Database::getInstance()->connect();
}

// Register PSR-4 style autoloader for namespaced module/core classes
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\Modules\\' => __DIR__ . '/modules/',
        'App\Core\\' => __DIR__ . '/core/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strpos($class, $prefix) === 0) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;

                // If the requested namespaced class is missing but a global class
                // with the same short name was loaded, alias it (core classes are global).
                $parts = explode('\\', $class);
                $shortName = end($parts);
                if (!class_exists($class, false) && class_exists($shortName, false)) {
                    class_alias($shortName, $class, false);
                }
                return;
            }
        }
    }
});

// Autoloader fallback: allow legacy/global short class names (e.g., ExchangeRateService)
// to resolve to their App\Core\ or App\Modules\ counterparts automatically.
spl_autoload_register(function (string $class): void {
    if (strpos($class, '\\') !== false) {
        return;
    }
    foreach (['App\\Core\\', 'App\\Modules\\'] as $prefix) {
        $fqcn = $prefix . $class;
        if (class_exists($fqcn, true) && !class_exists($class, false)) {
            class_alias($fqcn, $class, false);
            return;
        }
    }
}, true, true);


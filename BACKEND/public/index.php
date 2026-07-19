<?php

// Initialize error handler
require_once __DIR__ . '/../bootstrap.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Screen-Width, X-Screen-Height, X-Device-Type, x-screen-size, x-screen-width, x-screen-height, x-device-type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request URI
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = parse_url($requestUri, PHP_URL_PATH);

// Detect and strip base path for Apache Alias setups
// The app routes always start with /, /frontend/, /api/, /consumer/, /dashboard/, /kiosk/, /mobile/
// Find where the actual route starts in the URI
$routePatterns = ['/restoran/', '/frontend/', '/api/', '/consumer/', '/dashboard/', '/kiosk/', '/mobile/'];
$basePath = '';
foreach ($routePatterns as $pattern) {
    $pos = strpos($requestUri, $pattern);
    if ($pos !== false && $pos > 0) {
        $basePath = substr($requestUri, 0, $pos);
        break;
    }
}
// Check if URI ends with a known file extension (static files)
if (!$basePath && preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/', $requestUri)) {
    // Find /frontend/ or /css/ or /js/ in the path
    if (preg_match('/^(.*?)(\/frontend\/.*)$/', $requestUri, $m)) {
        $basePath = $m[1];
    }
}
// If URI is just a base path with optional trailing slash, it's the root
if (!$basePath) {
    // Check if this looks like a base path like /EBP/restaurant or /EBP/restaurant/
    // Only match single-level paths with no file extension and no subdirectory
    if ($requestUri !== '/' && strpos($requestUri, '/frontend/') === false && strpos($requestUri, '/api/') === false) {
        // Must not contain a file extension or nested path
        // e.g. /restoran/ is ok, /js/config.js is NOT a base path
        $trimmed = trim($requestUri, '/');
        $parts = explode('/', $trimmed);
        if (count($parts) === 1 && !preg_match('/\.(html|php|css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/', $parts[0])) {
            // Exclude known app directories
            $isAppDir = in_array('/' . $parts[0] . '/', ['/consumer/', '/dashboard/', '/kiosk/', '/mobile/']);
            if (!$isAppDir) {
                $basePath = rtrim($requestUri, '/');
            }
        }
    }
}

// Special handling for API routes - ensure base path is stripped correctly
// Only match /api/ (with trailing slash) or /api at end, not filenames like /api-client.js
if (!$basePath && (strpos($requestUri, '/api/') !== false || preg_match('/\/api$/', $requestUri))) {
    // If we have /api in the URI but no base path was detected,
    // it might be that the pattern matching failed. Try again.
    $pos = strpos($requestUri, '/api/');
    if ($pos === false) {
        $pos = strrpos($requestUri, '/api');
    }
    if ($pos > 0) {
        $basePath = substr($requestUri, 0, $pos);
    }
}

// Strip base path from request URI
if ($basePath && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
// Ensure leading slash
if ($requestUri === '' || $requestUri[0] !== '/') {
    $requestUri = '/' . $requestUri;
}

// Override REQUEST_URI so Router and other code see the stripped path
$_SERVER['REQUEST_URI'] = $requestUri;

// Normalize case: /FRONTEND/ -> /frontend/ for consistent routing
if (preg_match('/^\/FRONTEND\//', $requestUri)) {
    $requestUri = preg_replace('/^\/FRONTEND\//', '/frontend/', $requestUri, 1);
    $_SERVER['REQUEST_URI'] = $requestUri;
}

// Define frontend source directory
$frontendDir = __DIR__ . '/../../FRONTEND';

// Serve index.html for root path
if ($requestUri === '/' || $requestUri === '/index.html') {
    require_once $frontendDir . '/index.html';
    exit;
}

// Serve static files from public directory (map.html, etc.)
// Exclude app directories that have their own routing
$appDirs = ['/dashboard/', '/consumer/', '/kiosk/', '/mobile/'];
$isAppDir = false;
foreach ($appDirs as $dir) {
    if (strpos($requestUri, $dir) === 0) {
        $isAppDir = true;
        break;
    }
}

if (!$isAppDir && preg_match('/\.(html|css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/', $requestUri)) {
    // MIME type map for common web files
    $mimeMap = [
        '.js' => 'application/javascript',
        '.css' => 'text/css',
        '.html' => 'text/html',
        '.json' => 'application/json',
        '.png' => 'image/png',
        '.jpg' => 'image/jpeg',
        '.jpeg' => 'image/jpeg',
        '.gif' => 'image/gif',
        '.svg' => 'image/svg+xml',
        '.ico' => 'image/x-icon',
        '.woff' => 'font/woff',
        '.woff2' => 'font/woff2',
        '.ttf' => 'font/ttf',
    ];
    $getMimeType = function($path) use ($mimeMap) {
        $ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
        return $mimeMap[$ext] ?? (mime_content_type($path) ?: 'application/octet-stream');
    };
    // Try BACKEND/public first (for map.html etc.)
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . ltrim($requestUri, '/');
    if (file_exists($filePath) && !is_dir($filePath)) {
        header("Content-Type: " . $getMimeType($filePath));
        readfile($filePath);
        exit;
    }
    // Try FRONTEND directory
    $filePath = $frontendDir . DIRECTORY_SEPARATOR . ltrim($requestUri, '/');
    if (file_exists($filePath) && !is_dir($filePath)) {
        header("Content-Type: " . $getMimeType($filePath));
        readfile($filePath);
        exit;
    }
}

// Serve frontend static files (CSS, JS, images)
if (strpos($requestUri, '/frontend/') === 0) {
    $filePath = $frontendDir . substr($requestUri, strlen('/frontend'));
    if (file_exists($filePath) && !is_dir($filePath)) {
        $ext = '.' . pathinfo($filePath, PATHINFO_EXTENSION);
        $mime = $mimeMap[$ext] ?? (mime_content_type($filePath) ?: 'application/octet-stream');
        header("Content-Type: $mime");
        readfile($filePath);
        exit;
    }
}

// Serve frontend consumer app
if (strpos($requestUri, '/consumer') === 0 || strpos($requestUri, '/frontend/consumer') === 0) {
    $basePathLen = strpos($requestUri, '/frontend/consumer') === 0 ? strlen('/frontend/consumer') : strlen('/consumer');
    $subPath = substr($requestUri, $basePathLen);
    $filePath = $frontendDir . '/consumer' . $subPath;
    if (file_exists($filePath) && !is_dir($filePath)) {
        $mimeType = mime_content_type($filePath);
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    } else {
        require_once $frontendDir . '/consumer/index.html';
        exit;
    }
}

// Serve frontend dashboard app
if (strpos($requestUri, '/dashboard') === 0 || strpos($requestUri, '/frontend/dashboard') === 0) {
    $basePathLen = strpos($requestUri, '/frontend/dashboard') === 0 ? strlen('/frontend/dashboard') : strlen('/dashboard');
    $subPath = substr($requestUri, $basePathLen);
    $filePath = $frontendDir . '/dashboard' . $subPath;
    if (file_exists($filePath) && !is_dir($filePath)) {
        $mimeType = mime_content_type($filePath);
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    } else {
        require_once $frontendDir . '/dashboard/index.html';
        exit;
    }
}

// Serve frontend kiosk app
if (strpos($requestUri, '/kiosk') === 0 || strpos($requestUri, '/frontend/kiosk') === 0) {
    $basePathLen = strpos($requestUri, '/frontend/kiosk') === 0 ? strlen('/frontend/kiosk') : strlen('/kiosk');
    $subPath = substr($requestUri, $basePathLen);
    $filePath = $frontendDir . '/kiosk' . $subPath;
    if (file_exists($filePath) && !is_dir($filePath)) {
        $mimeType = mime_content_type($filePath);
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    } else {
        require_once $frontendDir . '/kiosk/index.html';
        exit;
    }
}

// Serve frontend mobile app
if (strpos($requestUri, '/mobile') === 0 || strpos($requestUri, '/frontend/mobile') === 0) {
    $basePathLen = strpos($requestUri, '/frontend/mobile') === 0 ? strlen('/frontend/mobile') : strlen('/mobile');
    $subPath = substr($requestUri, $basePathLen);
    $filePath = $frontendDir . '/mobile' . $subPath;
    if (file_exists($filePath) && !is_dir($filePath)) {
        $mimeType = mime_content_type($filePath);
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    } else {
        require_once $frontendDir . '/mobile/index.html';
        exit;
    }
}

// Serve API routes for /api paths
if (strpos($requestUri, '/api') === 0) {
    require_once __DIR__ . '/../bootstrap.php';
    require_once __DIR__ . '/../routes/api.php';

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Screen-Width, X-Screen-Height, X-Device-Type, x-screen-size, x-screen-width, x-screen-height, x-device-type");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
} else {
    // Try serving other FRONTEND pages (login.html, landing.html, reset-password.html, etc.)
    $filePath = $frontendDir . $requestUri;
    if (file_exists($filePath) && !is_dir($filePath)) {
        $mimeType = mime_content_type($filePath);
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    }
    // Return 404 for non-API, non-root paths
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Route not found', 'errors' => []]);
    exit;
}

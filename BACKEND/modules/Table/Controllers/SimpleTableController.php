<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleTableController extends \App\Core\BaseController
{
    // Simple endpoint to get tables without complex middleware
    public function getTables($request = null)
    {
        $db = db();

        // Get screen size from headers or parameters
        $headers = getallheaders();
        $screenSize = \ScreenSizeHelper::getScreenSize($headers, $request['query'] ?? []);

        // Get pagination with screen size defaults
        $pagination = \ScreenSizeHelper::getPaginationParams($request['query'] ?? [], $screenSize, 'tables');
        $limit = $pagination['limit'];
        $page = $pagination['page'];
        $offset = ($page - 1) * $limit;

        $sql = "SELECT table_id, table_number, table_name, capacity, status
                FROM tables
                ORDER BY table_number
                LIMIT ? OFFSET ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Apply screen size filtering
        $result = [
            'success' => true,
            'data' => $tables,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'screen_size' => $screenSize
            ]
        ];

        $result = \ScreenSizeHelper::applyScreenSizeFilter($result, $screenSize, 'tables');

        Response::json($result);
    }
}

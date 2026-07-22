<?php

declare(strict_types=1);

namespace App\Core;

final class Pagination
{
    public static function fromQuery(array $query, int $defaultPerPage = 20, int $maximumPerPage = 100): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = min(max(1, (int) ($query['per_page'] ?? $defaultPerPage)), $maximumPerPage);

        return [
            'page' => $page,
            'per_page' => $perPage,
            'offset' => ($page - 1) * $perPage,
        ];
    }

    public static function metadata(int $total, int $page, int $perPage): array
    {
        return [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }
}

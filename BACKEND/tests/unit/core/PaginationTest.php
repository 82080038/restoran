<?php

use App\Core\Pagination;
use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    public function testBuildsBoundedPaginationFromQuery(): void
    {
        $pagination = Pagination::fromQuery(['page' => '3', 'per_page' => '500']);

        $this->assertSame(3, $pagination['page']);
        $this->assertSame(100, $pagination['per_page']);
        $this->assertSame(200, $pagination['offset']);
    }

    public function testBuildsPaginationMetadata(): void
    {
        $metadata = Pagination::metadata(41, 2, 20);

        $this->assertSame(41, $metadata['total']);
        $this->assertSame(3, $metadata['total_pages']);
    }
}

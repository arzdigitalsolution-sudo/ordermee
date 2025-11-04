<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/helpers/helpers.php';

final class HelpersTest extends TestCase
{
    public function testPaginateCalculatesPages(): void
    {
        $result = ClickCart\Helpers\paginate(55, 10, 3);
        $this->assertSame(6, $result['pages']);
        $this->assertTrue($result['has_prev']);
        $this->assertTrue($result['has_next']);
    }
}

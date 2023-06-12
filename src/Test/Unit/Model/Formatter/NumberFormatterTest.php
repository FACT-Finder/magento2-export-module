<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Formatter;

use PHPUnit\Framework\TestCase;

/**
 * @covers NumberFormatter
 */
class NumberFormatterTest extends TestCase
{
    private NumberFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new NumberFormatter();
    }

    public function testItFormatsTheNumberWithRightPrecision()
    {
        $this->assertSame('42.21', $this->formatter->format(42.2051));
        $this->assertSame('42', $this->formatter->format(42.2051, 0));
        $this->assertSame('42.2', $this->formatter->format(42.2051, 1));
        $this->assertSame('42.21', $this->formatter->format(42.2051, 2));
        $this->assertSame('42.205', $this->formatter->format(42.2051, 3));
    }
}

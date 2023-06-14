<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Filter;

use Factfinder\Export\Api\Filter\FilterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers TextFilter
 */
class TextFilterTest extends TestCase
{
    private TextFilter $filter;

    protected function setUp(): void
    {
        $this->filter = new TextFilter();
    }

    public function testItIsFilter()
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    /**
     * @dataProvider textProvider
     */
    public function testShouldFiltersTheText(string $from, string $to): void
    {
        $this->assertSame($to, $this->filter->filterValue($from));
    }

    public static function textProvider(): array
    {
        return [
            'strip whitespace'           => ['   FACT-Finder   ', 'FACT-Finder'],
            'remove/compact whitespace'  => ["  FACT \n\r\t\n   Finder", 'FACT Finder'],
            'convert html entities'      => ['Gie&szlig;en M&Uuml;NCHEN Forl&igrave;', 'Gießen MÜNCHEN Forlì'],
            'drop 2-byte chars'          => ['Elisa EverCool&trade; Tee', 'Elisa EverCool™ Tee'],
            'allowed symbols'            => ['!"#$%&\'()*+,-./:;=?@[\]_{|}~|', '!"#$%&\'()*+,-./:;=?@[\]_{|}~|'],
            'keep utf8 #1'               => ['Österreich', 'Österreich'],
            'keep utf8 #2'               => ['Wrocław', 'Wrocław'],
            'keep utf8 #3'               => ['skříň', 'skříň'],
            'keep utf8 #4'               => ['LumaTech™ Tee', 'LumaTech™ Tee'],
            'strip HTML #1'              => ['<h1>FACT-Finder</h1>', 'FACT-Finder'],
            'strip HTML #2'              => ['FACT-<span>Finder</span>', 'FACT-Finder'],
            'strip HTML, keep blocks #1' => ['<h1>FACT-Finder</h1><p>Omikron GmbH</p>', 'FACT-Finder Omikron GmbH'],
            'strip HTML, keep blocks #2' => ['FACT-Finder<br>Omikron GmbH', 'FACT-Finder Omikron GmbH'],
            'non printable chars'        => ['Schulfüller Pelikano Junior P68L türkis', 'Schulfüller Pelikano Junior P68L türkis'],
            'non-breaking space'         => ['chevaux contre', 'chevaux contre'],
        ];
    }
}

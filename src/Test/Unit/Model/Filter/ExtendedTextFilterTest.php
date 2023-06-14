<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Filter;

use Factfinder\Export\Api\Filter\FilterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers ExtendedTextFilter
 */
class ExtendedTextFilterTest extends TestCase
{
    private TextFilter $filter;

    protected function setUp(): void
    {
        $this->filter = new ExtendedTextFilter();
    }
    public function testItIsFilter(): void
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    public function testShouldRemovesForbiddenCharacters(): void
    {
        $this->assertSame($this->filter->filterValue('Remove#all|forbidden=chars'), 'Remove all forbidden chars');
    }
}

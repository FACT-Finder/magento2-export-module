<?php

namespace Factfinder\Export\Model\Export\Catalog;

use Factfinder\Export\Api\Filter\FilterInterface;
use Factfinder\Export\Model\Filter\TextFilter;
use Factfinder\Export\Model\Formatter\NumberFormatter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers AttributeValuesExtractor
 */
class AttributeValuesExtractorTest extends TestCase
{
    private AttributeValuesExtractor $attributeExtractor;
    private MockObject|NumberFormatter $numberFormatter;

    protected function setUp(): void
    {
        $this->filterMock = $this->createMock(FilterInterface::class, []);
        $this->numberFormatter = $this->createMock(NumberFormatter::class, []);
        $this->attributeExtractor = new AttributeValuesExtractor(new TextFilter(), $this->numberFormatter);
    }

    public function testShouldReturnsScalarValue()
    {
        $attributeValue  = 'Value';
        $productMock = $this->createConfiguredMock(Product::class, [
            'getDataUsingMethod' => $attributeValue
        ]);
        $attributeMock = $this->createConfiguredMock(Attribute::class, [
            'getAttributeCode' => 'test-atttribute',
            'getFrontendInput' => 'varchar',
        ]);
        $attributeValues = $this->attributeExtractor->getAttributeValues($productMock, $attributeMock);
        $this->assertEquals([$attributeValue], $attributeValues);
    }

    public function testShouldReturnsEmptyStringOnNullValue()
    {
        $productMock = $this->createConfiguredMock(Product::class, [
            'getDataUsingMethod' => null
        ]);
        $attributeMock   = $this->createConfiguredMock(Attribute::class, [
            'getAttributeCode' => 'test-atttribute',
            'getFrontendInput' => 'varchar',
        ]);
        $attributeValues = $this->attributeExtractor->getAttributeValues($productMock, $attributeMock);
        $this->assertEquals([], $attributeValues);
    }
}

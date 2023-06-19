<?php

declare(strict_types=1);

namespace Omikron\Factfinder\Model\Export\Catalog\ProductType;

use Factfinder\Export\Api\Filter\FilterInterface;
use Factfinder\Export\Model\Export\Catalog\Entity\ProductVariationFactory;
use Factfinder\Export\Model\Export\Catalog\ProductType\ConfigurableDataProvider;
use Factfinder\Export\Model\Formatter\NumberFormatter;
use Factfinder\Export\Test\TestHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurableDataProviderTest extends TestCase
{
    private ConfigurableDataProvider $configurableDataProvider;
    private MockObject|ProductRepositoryInterface $repositoryMock;
    private MockObject|NumberFormatter $numberFormatMock;
    private MockObject|ConfigurableProductType $configurableProductTypeMock;
    private MockObject|FilterInterface $filterMock;
    private MockObject|ProductVariationFactory $variantFactoryMock;
    private MockObject|SearchCriteriaBuilder $builderMock;
    private MockObject|Product $productMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->numberFormatMock = $this->createMock(NumberFormatter::class);
        $this->configurableProductTypeMock = $this->createMock(ConfigurableProductType::class);
        $this->filterMock = $this->createMock(FilterInterface::class);
        $this->variantFactoryMock = $this->getMockBuilder(ProductVariationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->builderMock = $this->createMock(SearchCriteriaBuilder::class);

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurableDataProvider = new ConfigurableDataProvider(
            $this->productMock,
            $this->numberFormatMock,
            $this->configurableProductTypeMock,
            $this->filterMock,
            $this->variantFactoryMock,
            $this->repositoryMock,
            $this->builderMock
        );
    }

    /**
     * @covers ConfigurableDataProvider::valueOrEmptyStr
     */
    public function testShouldReturnStringOnStringValue()
    {
        $valueOrEmptyStrMethod = TestHelper::invokeMethod($this->configurableDataProvider, 'valueOrEmptyStr', ['test']);
        $this->assertEquals('test', $valueOrEmptyStrMethod);
    }

    /**
     * @covers ConfigurableDataProvider::valueOrEmptyStr
     */
    public function testShouldReturnEmptyStringOnNullValue()
    {
        $valueOrEmptyStrMethod = TestHelper::invokeMethod($this->configurableDataProvider, 'valueOrEmptyStr', [null]);
        $this->assertEquals('', $valueOrEmptyStrMethod);
    }

    /**
     * @covers ConfigurableDataProvider::valueOrEmptyStr
     */
    public function testShouldReturnEmptyStringOnBoolValue()
    {
        $valueOrEmptyStrMethod = TestHelper::invokeMethod($this->configurableDataProvider, 'valueOrEmptyStr', [false]);
        $this->assertEquals('', $valueOrEmptyStrMethod);
    }

    /**
     * @covers ConfigurableDataProvider::valueOrEmptyStr
     */
    public function testShouldReturnemptyStringOnArrayValue()
    {
        $valueOrEmptyStrMethod = TestHelper::invokeMethod($this->configurableDataProvider, 'valueOrEmptyStr', [[]]);
        $this->assertEquals('', $valueOrEmptyStrMethod);
    }

    /**
     * @covers ConfigurableDataProvider::getChildren
     */
    public function testShouldNoThrowErrorIfThereIsNoChlidrenIds()
    {
        $this->productMock->method('getId')->willReturn('1');
        $this->configurableProductTypeMock->method('getChildrenIds')->with('1')
            ->willReturn([]);
        $variants = TestHelper::invokeMethod($this->configurableDataProvider, 'getChildren', [$this->productMock]);
        $this->assertEquals([], $variants);
    }
}

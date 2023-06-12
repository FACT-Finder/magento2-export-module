<?php
declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog;

use Factfinder\Export\Test\ConsecutiveParams;
use Factfinder\Export\Test\TestHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Products
 */
class ProductsTest extends TestCase
{
    use ConsecutiveParams;
    private MockObject|StoreManagerInterface $storeManagerMock;
    private MockObject|ProductRepositoryInterface $productRepositoryMock;
    private MockObject|SearchCriteriaBuilder $searchCriteriaBuilderMock;
    private Products $products;

    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->storeManagerMock      = $this->createConfiguredMock(StoreManagerInterface::class, [
            'getStore' => $this->createConfiguredMock(
                Store::class,
                ['getId' => Store::DEFAULT_STORE_ID]
            )
        ]);

        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteriaBuilderMock->method('addFilter')->willReturn($this->searchCriteriaBuilderMock);
        $this->searchCriteriaBuilderMock->method('setPageSize')->willReturn($this->searchCriteriaBuilderMock);
        $this->searchCriteriaBuilderMock->method('setCurrentPage')->willReturn($this->searchCriteriaBuilderMock);

        $this->products = new Products(
            $this->productRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->storeManagerMock,
        );
    }

    public function test_is_iterable()
    {
        $this->assertTrue(is_iterable($this->products));
    }

    public function test_it_add_all_required_filters()
    {
        $this->searchCriteriaBuilderMock
            ->expects($this->exactly(3))
            ->method('addFilter')->with(...$this->consecutiveParams(
                [
                    'status',
                    Status::STATUS_ENABLED
                ],
                [
                    'store_id',
                    Store::DEFAULT_STORE_ID
                ],
                [
                    'visibility',
                    Visibility::VISIBILITY_NOT_VISIBLE
                ]
            ));

        TestHelper::invokeMethod($this->products, 'getQuery', [1]);
    }
}

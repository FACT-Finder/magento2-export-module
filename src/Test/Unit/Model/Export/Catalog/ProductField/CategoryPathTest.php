<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog\ProductField;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers CategoryPath
 */
class CategoryPathTest extends TestCase
{
    private CategoryPath $categoryPath;
    private CategoryRepositoryInterface|MockObject $repositoryMock;
    private AbstractModel|MockObject $productMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(CategoryRepositoryInterface::class);
        $this->repositoryMock->method('get')->willReturnMap(
            [
                [
                    1,
                    1,
                    $this->createConfiguredMock(
                        CategoryInterface::class,
                        [
                            'getName'     => 'Root Catlog',
                            'getPath'     => '1',
                            'getIsActive' => true,
                        ]
                    )
                ],
                [
                    2,
                    1,
                    $this->createConfiguredMock(
                        CategoryInterface::class,
                        [
                            'getName'     => 'Default Category',
                            'getPath'     => '1/2',
                            'getIsActive' => true,
                        ]
                    )
                ],
                [
                    3,
                    1,
                    $this->createConfiguredMock(
                        CategoryInterface::class,
                        [
                            'getName'     => 'Clothes',
                            'getPath'     => '1/2/3',
                            'getIsActive' => true,
                        ]
                    )
                ],
                [
                    4,
                    1,
                    $this->createConfiguredMock(
                        CategoryInterface::class,
                        [
                            'getName'     => 'Trousers & Pants',
                            'getPath'     => '1/2/3/4',
                            'getIsActive' => true,
                        ]
                    )
                ],
                [
                    5,
                    1,
                    $this->createConfiguredMock(
                        CategoryInterface::class,
                        [
                            'getName'     => '5/6 Length Trousers',
                            'getPath'     => '1/2/3/4/5',
                            'getIsActive' => true,
                        ]
                    )
                ],
                [
                    6,
                    1,
                    $this->createConfiguredMock(
                        CategoryInterface::class,
                        [
                            'getName'     => 'Short Trousers',
                            'getPath'     => '1/2/3/4/6',
                            'getIsActive' => true,
                        ]
                    )
                ],
                [
                    7,
                    1,
                    $this->createConfiguredMock(
                        CategoryInterface::class,
                        [
                            'getName'     => 'Not Active category',
                            'getPath'     => '1/2/7',
                            'getIsActive' => false,
                        ]
                    )
                ],
            ]
        );
        $this->productMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStore', 'getCategoryIds'])
            ->getMock();

        $this->productMock->method('getStore')
            ->willReturn($this->createConfiguredMock(Store::class, [
                'getId'             => 1,
                'getRootCategoryId' => 2,
            ]));

        $this->categoryPath = new CategoryPath($this->repositoryMock, 'CategoryPath');
    }

    public function testMultipleCategoryBranchesWillBeExported()
    {
        $this->productMock->method('getCategoryIds')->willReturn(['5', '6']);
        $path = $this->categoryPath->getValue($this->productMock);
        $this->assertEquals(
            $path,
            'Clothes/Trousers+%26+Pants/5%2F6+Length+Trousers|Clothes/Trousers+%26+Pants/Short+Trousers'
        );
    }
}

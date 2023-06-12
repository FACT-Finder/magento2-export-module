<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog\Entity;

use Factfinder\Export\Model\Export\Catalog\FieldProvider;
use Factfinder\Export\Model\Export\Catalog\ProductField\FilterAttributes;
use Factfinder\Export\Model\Export\Catalog\ProductField\ProductImage;
use Factfinder\Export\Model\Formatter\NumberFormatter;
use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers ProductVariation
 */
class ProductVariationTest extends TestCase
{
    private MockObject|AbstractModel $variantMock;

    private array $configurableProductData = [
        'ProductNumber' => 'sku-configurable',
        'Price'         => '8.99',
        'HasVariants'   => 1,
        'MagentoId'     => 1,
        'ImageUrl'      => 'http://random-variant-image.png',
    ];

    protected function setUp(): void
    {
        $this->variantMock = $variantMock = $this->createConfiguredMock(
            Product::class,
            [
                'getSku'        => 'sku-variant',
                'getFinalPrice' => '9.99',
                'isAvailable'   => true,
                'getId'         => 2
            ]
        );
    }

    public function testVariantDataWillOverrideParent()
    {
        $fieldProviderMock = $this->createConfiguredMock(
            FieldProvider::class,
            [
                'getVariantFields' => [
                    'ImageUrl' => $this->createConfiguredMock(
                        ProductImage::class,
                        [
                            'getName'  => 'ImageUrl',
                            'getValue' => 'http://specific-variant-image.png'
                        ]
                    )
                ]
            ]
        );

        $productVariation = new ProductVariation(
            $this->variantMock,
            $this->createMock(Product::class),
            new NumberFormatter(),
            $fieldProviderMock,
            $this->configurableProductData
        );

        $this->assertEquals([
            'ProductNumber' => 'sku-variant',
            'Price'         => '9.99',
            'Availability'  => 1,
            'MagentoId'     => 2,
            'HasVariants'   => 0,
            'ImageUrl'      => 'http://specific-variant-image.png',
        ] + $this->configurableProductData, $productVariation->toArray());
    }

    public function testConfigurableAttributeshouldBeMergedWithFilterAttributes()
    {
        $fieldProviderMock = $this->createConfiguredMock(
            FieldProvider::class,
            [
                'getVariantFields' => [
                    'FilterAttributes' => $this->createConfiguredMock(
                        FilterAttributes::class,
                        [
                            'getName'  => 'FilterAttributes',
                            'getValue' => '|Eco Collection=No|New=No|Price=52.00|Quantity=In Stock|',
                        ]
                    )
                ]
            ]
        );

        $productVariation = new ProductVariation(
            $this->variantMock,
            $this->createMock(Product::class),
            new NumberFormatter(),
            $fieldProviderMock,
            ['FilterAttributes' => '|Color=Red|Size=XS|'] + $this->configurableProductData
        );

        $this->assertEquals(
            '|Color=Red|Size=XS|Eco Collection=No|New=No|Price=52.00|Quantity=In Stock|',
            $productVariation->toArray()['FilterAttributes']
        );
    }
}

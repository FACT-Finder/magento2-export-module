<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog\ProductType;

use Factfinder\Export\Api\Export\ExportEntityInterface;
use Factfinder\Export\Api\Filter\FilterInterface;
use Factfinder\Export\Model\Export\Catalog\Entity\ProductVariationFactory;
use Factfinder\Export\Model\Formatter\NumberFormatter;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ConfigurableDataProvider extends SimpleDataProvider
{
    public function __construct(
        protected Product $product,
        protected NumberFormatter $numberFormatter,
        private readonly ConfigurableProductType $productType,
        private readonly FilterInterface $filter,
        private readonly ProductVariationFactory $variationFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly SearchCriteriaBuilder $builder,
        protected array $productFields = []
    ) {
        parent::__construct($product, $numberFormatter, $productFields);
    }

    public function getEntities(): iterable
    {
        yield from parent::getEntities();
        yield from array_map($this->productVariation($this->product), $this->getChildren($this->product));
    }

    public function toArray(): array
    {
        $data = ['HasVariants' => 1] + parent::toArray();

        //flattening the array, array_merge must stay
        $options = array_merge([], ...array_values($this->getOptions($this->product)));

        if ($options) {
            $data['FilterAttributes'] = ($data['FilterAttributes'] ?: '|') . implode('|', array_unique($options)) . '|';
        }

        return $data;
    }

    private function getOptions(Product $product): array
    {
        $sanitize = fn(mixed $phrase): string => $this->filter->filterValue($this->valueOrEmptyStr($phrase));

        return array_reduce($this->productType->getConfigurableOptions($product), function (array $res, array $option) use ($sanitize) {
            foreach ($option as ['sku' => $sku, 'super_attribute_label' => $label, 'option_title' => $value]) {
                $res[$sku][] = "{$sanitize($label)}={$sanitize($value)}";
            }

            return $res;
        }, []);
    }

    private function productVariation(Product $product): callable
    {
        $options = $this->getOptions($product);
        $data = parent::toArray();

        return function (Product $variation) use ($options, $product, $data): ExportEntityInterface {
            $sku = $variation->getSku();

            return $this->variationFactory->create([
                'product' => $variation,
                'configurable' => $product,
                'data' => ['FilterAttributes' => '|' . implode('|', $options[$sku] ?? []) . '|'] + $data,
            ]);
        };
    }

    /**
     * phpcs:disable PSR2.ControlStructures.ControlStructureSpacing.SpacingAfterOpenBrace
     *
     * @param Product $product
     *
     * @return ProductInterface[]
     */
    private function getChildren(Product $product): array
    {
        $childrenIds = $this->productType->getChildrenIds($product->getId());

        //if $childrenIds is empty the entity_id filter will throw an SQL syntax error
        if (
            empty($childrenIds)
            || empty($childrenIds[0])
        ) {
            return [];
        }

        return $this->productRepository
            ->getList($this->builder->addFilter('entity_id', $childrenIds, 'in')
            ->create())
            ->getItems();
    }

    private function valueOrEmptyStr(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}

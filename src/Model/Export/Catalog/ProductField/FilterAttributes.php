<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog\ProductField;

use Factfinder\Export\Api\Export\FieldInterface;
use Factfinder\Export\Api\Filter\FilterInterface;
use Factfinder\Export\Model\Config\ExportConfig;
use Factfinder\Export\Model\Export\Catalog\AttributeValuesExtractor;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Model\AbstractModel;

class FilterAttributes implements FieldInterface
{
    protected bool $numerical = false;
    protected string $name = 'FilterAttributes';

    /**
     *  [storeId => string[]]
     */
    private array $attributes = [];

    public function __construct(
        private readonly ExportConfig $exportConfig,
        private readonly ProductResource $productResource,
        private readonly FilterInterface $filter,
        private readonly AttributeValuesExtractor $valuesExtractor,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(AbstractModel $product): string
    {
        $values = '';

        foreach ($this->getAttributes((int) $product->getStoreId()) as $label => $attribute) {
            $attributeValues = implode('#', $this->valuesExtractor->getAttributeValues($product, $attribute));

            if ($attributeValues) {
                $values .= "|{$label}={$attributeValues}";
            }
        }

        return $values ? "{$values}|" : '';
    }

    /**
     * @param int $storeId
     *
     * @return Attribute[]
     */
    private function getAttributes(int $storeId): array
    {
        if (!isset($this->attributes[$storeId])) {
            $codes = $this->exportConfig->getMultiAttributes($storeId, $this->numerical);
            $attributes = array_filter(array_map([$this->productResource, 'getAttribute'], $codes));
            $labels = array_map($this->getAttributeLabel($storeId), $attributes);
            $this->attributes[$storeId] = array_combine($labels, $attributes);
        }

        return $this->attributes[$storeId];
    }

    private function getAttributeLabel(int $storeId): callable
    {
        return fn (Attribute $attribute): string => $this->filter->filterValue((string) $attribute->getStoreLabel($storeId));
    }
}

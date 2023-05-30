<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog\ProductField;

use Factfinder\Export\Api\Export\FieldInterface;
use Factfinder\Export\Model\Export\Catalog\AttributeValuesExtractor;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;

class GenericField implements FieldInterface
{
    public function __construct(
        private readonly ProductAttributeRepositoryInterface $attributeRepository,
        private readonly AttributeValuesExtractor $valuesExtractor,
        private readonly StoreManagerInterface $storeManager,
        private readonly string $attributeCode,
    ) {
    }

    public function getName(): string
    {
        return (string) $this->getAttribute()->getStoreLabel($this->storeManager->getStore());
    }

    /**
     * @param Product $product
     */
    public function getValue(AbstractModel $product): string
    {
        return implode('|', $this->valuesExtractor->getAttributeValues($product, $this->getAttribute()));
    }

    private function getAttribute(): Attribute
    {
        $this->attribute = $this->attribute ?? $this->attributeRepository->get($this->attributeCode);

        return $this->attribute;
    }
}

<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog\Entity;

use Factfinder\Export\Api\Export\ExportEntityInterface;
use Factfinder\Export\Api\Export\FieldInterface;
use Factfinder\Export\Model\Export\Catalog\FieldProvider;
use Factfinder\Export\Model\Formatter\NumberFormatter;
use Magento\Catalog\Model\Product;

class ProductVariation implements ExportEntityInterface
{
    private FieldProvider $fieldprovider;

    /** @var string[] */
    private array $configurableData;

    public function __construct(
        private readonly Product $product,
        private readonly Product $configurable,
        private readonly NumberFormatter $numberFormatter,
        FieldProvider $variantFieldProvider,
        array $data = []
    ) {
        $this->configurableData = $data;
        $this->fieldprovider    = $variantFieldProvider;
    }

    public function getId(): int
    {
        return (int) $this->product->getId();
    }

    public function toArray(): array
    {
        $baseData = [
                'ProductNumber' => (string) $this->product->getSku(),
                'Price' => $this->numberFormatter->format((float) $this->product->getFinalPrice()),
                'Availability' => (int) $this->product->isAvailable(),
                'HasVariants' => 0,
                'MagentoId' => $this->getId(),
            ] + $this->configurableData;

        list($filterAttributes, $restFields) = $this->extractFilterAttributes($this->fieldprovider->getVariantFields());
        $parentAttributes = $this->configurableData['FilterAttributes'] ?? '';
        $variantAttributes = $filterAttributes ? $filterAttributes->getValue($this->product) : '';
        $splicedFilterAttributes = str_replace('||', '|', $parentAttributes . $variantAttributes);

        return ($splicedFilterAttributes ? ['FilterAttributes' => $splicedFilterAttributes] : [])
            + array_reduce(
                $restFields,
                fn (array $result, FieldInterface $field): array => [$field->getName() => $field->getValue($this->product)] + $result,
                $baseData
            );
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getConfigurable(): Product
    {
        return $this->configurable;
    }

    private function extractFilterAttributes(array $fields): array
    {
        $withoutFilterAttributes = array_filter($fields, fn (FieldInterface $field): bool => $field->getName() !== 'FilterAttributes');
        $filterAttributes = $fields['FilterAttributes'] ?? [];

        return [$filterAttributes, $withoutFilterAttributes];
    }
}

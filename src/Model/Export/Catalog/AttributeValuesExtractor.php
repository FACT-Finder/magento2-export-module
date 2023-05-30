<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog;

use Factfinder\Export\Api\Filter\FilterInterface;
use Factfinder\Export\Model\Formatter\NumberFormatter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use UnexpectedValueException;
use DateTime;

class AttributeValuesExtractor
{
    public function __construct(
        private readonly FilterInterface $filter,
        private readonly NumberFormatter $numberFormatter,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Exception
     */
    //phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
    public function getAttributeValues(Product $product, Attribute $attribute): array
    {
        $code   = $attribute->getAttributeCode();
        $value  = $product->getDataUsingMethod($code) ?? $product->getData($code);
        $values = [];

        switch ($attribute->getFrontendInput()) {
            case 'boolean':
                $values[] = $value ? __('Yes') : __('No');
                break;
            case 'price':
                $values[] = $this->numberFormatter->format((float) $value);
                break;
            case 'select':
                $value = $product->getAttributeText($code);

                if (is_array($value)) {
                    $value = reset($value);
                }

                $values[] = (string) $value;
                break;
            case 'multiselect':
                $values = (array) $product->getAttributeText($code);
                break;
            case 'date':
                $values[] = $value ? (new DateTime($value))->format("Y-m-d'T'") : '';
                break;
            case 'datetime':
                $values[] = $value ? (new DateTime($value))->format("Y-m-d'T'H:i:sP") : '';
                break;
            default:
                if (!is_scalar($value)) {
                    switch (true) {
                        case $value === null:
                            $value = '';
                            break;
                        default:
                            $msg =
                                "Attribute '{$code}' could not be exported. Please consider writing your own field model";
                            throw new UnexpectedValueException($msg);
                    }
                }
                $values[] = (string) $value;
                break;
        }

        return array_filter(
            array_map(
                [
                    $this->filter,
                    'filterValue',
                ],
                $values
            )
        );
    }
}

<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog;

use Factfinder\Export\Api\Export\FieldProviderInterface;

class FieldProvider implements FieldProviderInterface
{
    private ?array $cachedFields;
    private ?array $cachedVariantFields;
    public function __construct(
        private readonly array $productFields = [],
        private readonly array $variantFields = [],
    ) {
    }

    public function getVariantFields(): array
    {
        if (!isset($this->cachedVariantFields)) {
            $this->cachedVariantFields = $this->getFrom($this->variantFields);
        }

        return $this->cachedVariantFields;
    }

    public function getFields(): array
    {
        if (!isset($this->cachedFields)) {
            $this->cachedFields = $this->getFrom($this->productFields);
        }
        return $this->cachedFields;
    }

    public function getFrom(array $fields): array
    {
        /**
         * implement GenericFieldFactory in the future
         */

        return $fields;
    }
}

<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog\Entity;

use Factfinder\Export\Model\Export\Catalog\FieldProvider;
use Factfinder\Export\Model\Formatter\NumberFormatter;

class ProductVariationFactory
{
    public function __construct(
        private readonly NumberFormatter $numberFormatter,
        private readonly FieldProvider $fieldProvider,
    ) {
    }

    public function create(array $data): ProductVariation
    {
        return new ProductVariation(
            $data['product'],
            $data['configurable'],
            $this->numberFormatter,
            $this->fieldProvider,
            $data['data'],
        );
    }
}

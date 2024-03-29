<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog\ProductType;

class GroupedDataProvider extends SimpleDataProvider
{
    public function toArray(): array
    {
        $price = (float) $this->product->getPriceInfo()->getPrice('final_price')->getValue();

        return ['Price' => $this->numberFormatter->format($price)] + parent::toArray();
    }
}

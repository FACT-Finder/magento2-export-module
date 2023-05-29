<?php

declare(strict_types=1);

namespace Factfinder\Export\Api\Export;

/**
 * @api
 */
interface FieldProviderInterface
{
    /**
     * Method should return an array of fields which should be taken from configurable product
     *
     * @see FieldInterface
     */
    public function getFields(): array;

    /**
     * Method should return an array of fields which should be taken from variants
     *
     * @see FieldInterface
     */
    public function getVariantFields(): array;
}

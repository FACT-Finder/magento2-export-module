<?php

declare(strict_types=1);

namespace Factfinder\Export\Api\Export;

/**
 * @api
 */
interface DataProviderInterface
{
    /**
     * @return ExportEntityInterface[]
     */
    public function getEntities(): iterable;
}

<?php

declare(strict_types=1);

namespace Factfinder\Export\Api;

use Factfinder\Export\Api\Export\DataProviderInterface;

/**
 * @api
 */
interface ExporterInterface
{
    public function exportEntities(StreamInterface $stream, DataProviderInterface $dataProvider, array $columns): void;
}

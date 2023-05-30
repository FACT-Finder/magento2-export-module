<?php

declare(strict_types=1);

namespace Factfinder\Export\Model;

use Factfinder\Export\Api\Export\DataProviderInterface;
use Factfinder\Export\Api\ExporterInterface;
use Factfinder\Export\Api\Filter\FilterInterface;
use Factfinder\Export\Api\StreamInterface;

class Exporter implements ExporterInterface
{
    public function __construct(private readonly FilterInterface $filter)
    {
    }

    public function exportEntities(StreamInterface $stream, DataProviderInterface $dataProvider, array $columns): void
    {
        $emptyRecord = array_combine($columns, array_fill(0, count($columns), ''));

        foreach ($dataProvider->getEntities() as $entity) {
            $stream->addEntity($this->prepareRow($entity->toArray(), $emptyRecord));
        }
    }

    private function prepareRow(array $entityData, array $emptyRecord): array
    {
        return array_map([$this->filter, 'filterValue'], [...$emptyRecord, ...array_intersect_key($entityData, $emptyRecord)]);
    }
}

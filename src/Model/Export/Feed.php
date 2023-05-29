<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export;

use Factfinder\Export\Api\Export\DataProviderInterface;
use Factfinder\Export\Api\Export\FieldInterface;
use Factfinder\Export\Api\ExporterInterface;
use Factfinder\Export\Api\StreamInterface;

class Feed
{
    public function __construct(
        private readonly ExporterInterface $exporter,
        private readonly DataProviderInterface $dataProvider,
        private readonly array $fields,
        private readonly array $columns
    ) {}

    public function generate(StreamInterface $stream): void
    {
        $columns = $this->getColumns($this->fields);
        $stream->addEntity($columns);
        $this->exporter->exportEntities($stream, $this->dataProvider, $columns);
        $stream->finalize();
    }

    private function getColumns(array $fields): array
    {
        return array_values(array_unique([...$this->columns, ...array_map([$this, 'getFieldName'], $fields)]));
    }

    private function getFieldName(FieldInterface $field): string
    {
        return $field->getName();
    }
}

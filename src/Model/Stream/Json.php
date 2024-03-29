<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Stream;

use Factfinder\Export\Api\StreamInterface;

class Json implements StreamInterface
{
    private array $stream = [];

    public function addEntity(array $entity): void
    {
        $this->stream[] = $entity;
    }

    public function getContent(): string
    {
        return json_encode($this->stream);
    }

    public function finalize(): void //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    {
    }
}

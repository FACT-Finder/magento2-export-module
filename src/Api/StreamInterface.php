<?php

declare(strict_types=1);

namespace Factfinder\Export\Api;

/**
 * @api
 */
interface StreamInterface
{
    /**
     * This method allows to add logic that should be executed after the feed is generated
     */
    public function finalize(): void;
    public function addEntity(array $entity): void;
    public function getContent(): string;
}

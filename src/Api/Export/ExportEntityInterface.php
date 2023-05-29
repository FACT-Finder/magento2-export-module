<?php

declare(strict_types=1);

namespace Factfinder\Export\Api\Export;

/**
 * @api
 */
interface ExportEntityInterface
{
    public function getId(): int;
    public function toArray(): array;
}

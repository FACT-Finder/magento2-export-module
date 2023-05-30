<?php

declare(strict_types=1);

namespace Factfinder\Export\Api\Filter;

interface FilterInterface
{
    public function filterValue(string $value): string;
}

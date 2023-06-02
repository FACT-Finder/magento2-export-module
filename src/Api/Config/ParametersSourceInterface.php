<?php

declare(strict_types=1);

namespace Factfinder\Export\Api\Config;

/**
 * @api
 */
interface ParametersSourceInterface
{
    public function getParameters(): array;
}

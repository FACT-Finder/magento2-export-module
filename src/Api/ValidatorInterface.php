<?php

declare(strict_types=1);

namespace Factfinder\Export\Api;

use Factfinder\Export\Exception\ExportPreviewValidationException;

interface ValidatorInterface
{
    /**
     * @throws ExportPreviewValidationException
     */
    public function validate(): void;
}

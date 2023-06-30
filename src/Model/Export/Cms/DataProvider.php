<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Cms;

use Factfinder\Export\Api\Export\DataProviderInterface;
use Factfinder\Export\Api\Export\ExportEntityInterface;

class DataProvider implements DataProviderInterface
{
    public function __construct(
        private readonly Pages $pages,
        private readonly PageFactory $pageFactory,
        private readonly array $fields,
    ) {
    }

    /**
     * @return ExportEntityInterface[]
     */
    public function getEntities(): iterable
    {
        yield from [];

        foreach ($this->pages as $page) {
            yield $this->pageFactory->create(['page' => $page, 'pageFields' => $this->fields]);
        }
    }
}

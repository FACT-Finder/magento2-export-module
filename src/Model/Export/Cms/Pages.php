<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Cms;

use Factfinder\Export\Model\Config\CmsConfig;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Traversable;

class Pages implements \IteratorAggregate
{
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository,
        private readonly SearchCriteriaBuilder   $searchCriteriaBuilder,
        private readonly CmsConfig               $cmsConfig,
        private readonly StoreManagerInterface   $storeManager,
    ) {
    }

    /**
     * @return Traversable|PageInterface[]
     * @throws LocalizedException
     */
    public function getIterator(): Traversable
    {
        $query = $this->getQuery()->create();
        yield from $this->pageRepository->getList($query)->getItems();
    }

    protected function getQuery(): SearchCriteriaBuilder
    {
        $blacklist = $this->cmsConfig->getCmsBlacklist();

        if ($blacklist) {
            $this->searchCriteriaBuilder->addFilter('identifier', $blacklist, 'nin');
        }

        $inStores  = [Store::DEFAULT_STORE_ID, (int) $this->storeManager->getStore()->getId()];

        return $this->searchCriteriaBuilder->addFilter('store_id', $inStores, 'in');
    }
}

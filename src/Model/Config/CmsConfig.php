<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class CmsConfig
{
    private const PATH_DISABLE_CMS_PAGES = 'factfinder_export/cms_export/ff_export_cms_blacklist';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function getCmsBlacklist(int $scopeCode = null): array
    {
        $pages = (string) $this->scopeConfig->getValue(self::PATH_DISABLE_CMS_PAGES, 'store', $scopeCode);

        return array_filter(explode(',', $pages));
    }
}

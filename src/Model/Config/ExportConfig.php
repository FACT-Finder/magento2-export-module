<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class ExportConfig
{
    private const CONFIG_PATH = 'factfinder_export/export/attributes';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getMultiAttributes(?int $storeId = null, bool $numerical = false): array
    {
        return $this->getAttributeCodes($storeId, fn (array $row): bool => $row['multi'] && (bool) $row['numerical'] == $numerical);
    }

    public function getSingleFields(?int $storeId = null): array
    {
        return $this->getAttributeCodes($storeId, fn (array $row): bool => !$row['multi']);
    }

    private function getAttributeCodes(?int $storeId, callable $condition): array
    {
        $rows = array_filter($this->getConfigValue($storeId), $condition);

        return array_values(array_unique(array_column($rows, 'code')));
    }

    private function getConfigValue(?int $storeId): array
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH, ScopeInterface::SCOPE_STORES, $storeId);

        return array_map(
            fn (array $row): array => ['multi' => !!$row['multi']] + $row,
            (array) $this->serializer->unserialize($value ?: '[]')
        );
    }
}

<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Config;

use Factfinder\Export\Api\Config\ParametersSourceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Omikron\FactFinder\Communication\Version;

class CommunicationConfig implements ParametersSourceInterface
{
    private const PATH_CHANNEL = 'factfinder_export/general/channel';
    private const PATH_ADDRESS = 'factfinder_export/general/address';
    private const PATH_VERSION = 'factfinder_export/general/version';
    private const PATH_API_VERSION = 'factfinder_export/general/ff_api_version';
    private const PATH_IS_ENABLED = 'factfinder_export/general/is_enabled';
    private const PATH_DATA_TRANSFER_IMPORT = 'factfinder_export/data_transfer/ff_export_push_import_enabled';
    private const PATH_IS_LOGGING_ENABLED = 'factfinder_export/general/logging_enabled';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function getChannel(?int $scopeId = null): string
    {
        return (string) $this->scopeConfig->getValue(self::PATH_CHANNEL, ScopeInterface::SCOPE_STORES, $scopeId);
    }

    public function getAddress(): string
    {
        return (string) $this->scopeConfig->getValue(self::PATH_ADDRESS, ScopeInterface::SCOPE_STORES);
    }

    public function isChannelEnabled(?int $scopeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_IS_ENABLED, ScopeInterface::SCOPE_STORES, $scopeId);
    }

    public function isPushImportEnabled(?int $scopeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_DATA_TRANSFER_IMPORT, ScopeInterface::SCOPE_STORES, $scopeId);
    }

    public function getVersion(): string
    {
        return (string) $this->scopeConfig->getValue(self::PATH_VERSION, ScopeInterface::SCOPE_STORES);
    }

    public function isLoggingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_IS_LOGGING_ENABLED, ScopeInterface::SCOPE_STORES);
    }

    public function getParameters(): array
    {
        return [
                'url'     => $this->getServerUrl(),
                'version' => $this->getVersion(),
                'channel' => $this->getChannel(),
            ] + ($this->getVersion() === Version::NG ? ['api' => $this->getApiVersion()] : []);
    }

    public function getApiVersion(): string
    {
        return (string) $this->scopeConfig->getValue(self::PATH_API_VERSION, ScopeInterface::SCOPE_STORES) ?? 'v4';
    }

    private function getServerUrl(): string
    {
        return $this->getAddress();
    }
}

<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class FtpConfig
{
    private const FPT_UPLOAD_CONFIG_PATH = 'factfinder_export/data_transfer/ff_export_upload_';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function toArray(): array
    {
        return [
            'host' => $this->getConfig('host'),
            'user' => $this->getConfig('user'),
            'username' => $this->getConfig('user'), // adjustments for \Magento\Framework\Filesystem\Io\Sftp
            'password' => $this->getConfig('password'),
            'ssl' => (bool) $this->getConfig('use_ssl'),
            'passive' => true,
            'port' => (int) $this->getConfig('port') ?: 21,
            'key_passphrase' => $this->getConfig('key_passphrase'),
            'type' => $this->getConfig('type'),
            'authentication_type' => $this->getConfig('authentication_type'),
        ];
    }

    private function getConfig(string $field): string
    {
        return (string) $this->scopeConfig->getValue(
            sprintf('%s%s', self::FPT_UPLOAD_CONFIG_PATH, $field)
        );
    }

    public function getUploadDirectory(): string
    {
        return (string) $this->getConfig('dir');
    }

    public function getKeyFileName(): string
    {
        return 'factfinder/sftp/' . $this->getConfig('authentication_key');
    }

    public function getUserPassword(): string
    {
        return $this->getConfig('password');
    }

    public function getKeyPassphrase(): string
    {
        return $this->getConfig('key_passphrase');
    }
}

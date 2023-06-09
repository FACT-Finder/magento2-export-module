<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BasicAuth
{
    private const CONFIG_PATH_USERNAME = 'factfinder_export/basic_auth/ff_export_auth_user';
    private const CONFIG_PATH_PASSWORD = 'factfinder_export/basic_auth/ff_export_auth_password';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function authenticate(string $username, string $password): bool
    {
        return strcmp($username, $this->getUsername()) === 0 && strcmp($password, $this->getPassword()) === 0;
    }

    private function getUsername(): string
    {
        return (string) $this->scopeConfig->getValue(self::CONFIG_PATH_USERNAME, ScopeInterface::SCOPE_STORE);
    }

    private function getPassword(): string
    {
        return (string) $this->scopeConfig->getValue(self::CONFIG_PATH_PASSWORD, ScopeInterface::SCOPE_STORE);
    }
}

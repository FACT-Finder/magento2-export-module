<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as Scope;

final class AuthConfig
{
    private const PATH_USERNAME     = 'factfinder_export/general/username';
    private const PATH_PASSWORD     = 'factfinder_export/general/password';
    private const PATH_AUTH_PREFIX  = 'factfinder_export/general/prefix';
    private const PATH_AUTH_POSTFIX = 'factfinder_export/general/postfix';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function getUsername(): string
    {
        return (string) $this->scopeConfig->getValue(self::PATH_USERNAME, Scope::SCOPE_STORE);
    }

    public function getPassword(): string
    {
        return (string) $this->scopeConfig->getValue(self::PATH_PASSWORD, Scope::SCOPE_STORE);
    }

    public function getAuthenticationPrefix(): string
    {
        return (string) $this->scopeConfig->getValue(self::PATH_AUTH_PREFIX, Scope::SCOPE_STORE);
    }

    public function getAuthenticationPostfix(): string
    {
        return (string) $this->scopeConfig->getValue(self::PATH_AUTH_POSTFIX, Scope::SCOPE_STORE);
    }
}

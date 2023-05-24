<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Api;

use Factfinder\Export\Model\Config\AuthConfig;
use Magento\Framework\ObjectManagerInterface;
use Omikron\FactFinder\Communication\Credentials;

class CredentialsFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly AuthConfig $authConfig
    ) {}

    public function create(array $authData = null): Credentials
    {
        return $this->objectManager->create(
            Credentials::class, $authData ?? [
                'username' => $this->authConfig->getUsername(),
                'password' => $this->authConfig->getPassword(),
                'prefix'   => $this->authConfig->getAuthenticationPrefix(),
                'postfix'  => $this->authConfig->getAuthenticationPostfix(),
              ]
        );
    }
}

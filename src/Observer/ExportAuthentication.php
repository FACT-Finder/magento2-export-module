<?php

declare(strict_types=1);

namespace Factfinder\Export\Observer;

use Factfinder\Export\Model\Export\BasicAuth;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface as Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Authentication as Credentials;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ExportAuthentication implements ObserverInterface
{
    public function __construct(
        private readonly ActionFlag $actionFlag,
        private readonly BasicAuth $authentication,
        private readonly Credentials $credentials,
    ) {
    }

    public function execute(Observer $observer)
    {
        if (!$this->authentication->authenticate(...$this->credentials->getCredentials())) {
            $this->credentials->setAuthenticationFailed('FACT-Finder');
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
        }
    }
}

<?php

declare(strict_types=1);

namespace Factfinder\Export\Controller\Adminhtml\TestConnection;

use Factfinder\Export\Model\Api\CredentialsFactory;
use Factfinder\Export\Model\Config\AuthConfig;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Phrase;
use Omikron\FactFinder\Communication\Client\ClientBuilder;
use Omikron\FactFinder\Communication\Credentials;
use Omikron\FactFinder\Communication\Resource\AdapterFactory;
use Psr\Http\Client\ClientExceptionInterface;

class TestApiConnection extends Action
{
    private const OBSCURED_VALUE = '******';

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonResultFactory,
        private readonly CredentialsFactory $credentialsFactory,
        private readonly AuthConfig $authConfig,
        private readonly ClientBuilder $clientBuilder
    ) {
        parent::__construct($context);
    }

    public function execute(): ?Json
    {
        try {
            $request = $this->getRequest();
            $clientBuilder = $this->clientBuilder
                ->withCredentials($this->getCredentials($this->getRequest()->getParams()))
                ->withServerUrl($request->getParam('address'));
            $adapterFactory = new AdapterFactory(
                $clientBuilder,
                $request->getParam('version'),
                $request->getParam('ff_api_version')
            );
            $searchAdapter = $adapterFactory->getSearchAdapter();
            $searchAdapter->search($request->getParam('channel'), '*');
            $message = new Phrase('Connection successfully established.');
        } catch (ClientExceptionInterface $e) {
            $message = $e->getMessage();
        }

        return $this->jsonResultFactory->create()->setData(['message' => $message]);
    }

    private function getCredentials(array $params): Credentials
    {
        // If password was not edit, load it from module configuration
        if (!isset($params['password']) || $params['password'] === self::OBSCURED_VALUE) {
            $params['password'] = $this->authConfig->getPassword();
        }

        return $this->credentialsFactory->create($params);
    }
}

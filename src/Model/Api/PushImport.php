<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Api;

use Factfinder\Export\Model\Config\CommunicationConfig;
use Factfinder\Export\Model\Config\ExportConfig;
use Omikron\FactFinder\Communication\Client\ClientBuilder;
use Omikron\FactFinder\Communication\Client\ClientException;
use Omikron\FactFinder\Communication\Resource\AdapterFactory;
use Omikron\FactFinder\Communication\Version;

class PushImport
{
    private string $pushImportResult = '';

    public function __construct(
        private readonly ClientBuilder $clientBuilder,
        private readonly CredentialsFactory $credentialsFactory,
        private readonly CommunicationConfig $communicationConfig,
        private readonly ExportConfig $exportConfig,
    ) {
    }

    public function execute(int $storeId): bool
    {
        $clientBuilder = $this->clientBuilder
            ->withServerUrl($this->communicationConfig->getAddress())
            ->withCredentials($this->credentialsFactory->create());

        $adapterFactory = new AdapterFactory(
            $clientBuilder,
            $this->communicationConfig->getVersion(),
            $this->communicationConfig->getApiVersion()
        );
        $importAdapter = $adapterFactory->getImportAdapter();
        $channel = $this->communicationConfig->getChannel($storeId);
        $dataTypes = $this->exportConfig->getPushImportDataTypes($storeId);

        if (!$dataTypes) {
            return false;
        }

        if ($this->communicationConfig->getVersion() === Version::NG && $importAdapter->running($channel)) {
            throw new ClientException("Can't start a new import process. Another one is still going");
        }

        $responses = [];

        foreach ($dataTypes as $dataType) {
            $responses = [...$responses, ...$importAdapter->import($channel, $dataType)];
        }

        $this->pushImportResult = $this->prepareListFromPushImportResponses($responses);

        return true;
    }

    public function getPushImportResult(): string
    {
        return $this->pushImportResult;
    }

    private function prepareListFromPushImportResponses(array $responses): string
    {
        return strtolower($this->communicationConfig->getVersion()) === 'ng'
            ? $this->ngResponse($responses)
            : $this->standardResponse($responses);
    }

    private function ngResponse(array $responses): string
    {
        $list = '<ul>%s</ul>';
        $listContent = '';

        foreach ($responses as $response) {
            $importType = sprintf('<li><b>%s push import type</b></li>', $response['importType']);
            $statusList = sprintf(
                '<ul>%s</ul>',
                implode('', array_map(fn (string $message): string => sprintf('<li>%s</li>', $message), $response['statusMessages']))
            );
            $statusMessages = sprintf('<li><i>Status messages</i></li><li>%s</li>', $statusList);
            $importType .= $statusMessages;
            $listContent .= $importType;
        }

        return sprintf($list, $listContent);
    }

    private function standardResponse(array $responses): string
    {
        $list = '<ul>%s</ul>';
        $listContent = '';

        if (!empty($responses['status'])) {
            $statusList = sprintf(
                '<ul>%s</ul>',
                implode('', array_map(fn (string $message): string => sprintf('<li>%s</li>', $message), $responses['status']))
            );

            $statusMessages = sprintf('<li><i>Status messages</i></li><li>%s</li>', $statusList);
            $listContent .= $statusMessages;
        }

        return sprintf($list, $listContent);
    }
}

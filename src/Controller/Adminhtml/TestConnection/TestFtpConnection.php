<?php

declare(strict_types=1);

namespace Factfinder\Export\Controller\Adminhtml\TestConnection;

use Factfinder\Export\Model\Config\FtpConfig;
use Factfinder\Export\Model\FtpUploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Phrase;

class TestFtpConnection extends Action
{
    private const OBSCURED_VALUE = '******';

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonResultFactory,
        private readonly FtpUploader $ftpUploader,
        private readonly FtpConfig $ftpConfig
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $message = new Phrase('Connection successfully established.');

        try {
            $request = $this->getRequest();
            $params  = $this->getConfig($this->getRealValuesFromObscured($request->getParams())) + $this->ftpConfig->toArray();
            $this->ftpUploader->testConnection($params);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return $this->jsonResultFactory->create()->setData(['message' => $message]);
    }

    private function getConfig(array $params): array
    {
        $prefix   = 'ff_export_upload_';
        $filtered = array_filter($params, fn (string $key) => (bool) preg_match("#^{$prefix}#", $key), ARRAY_FILTER_USE_KEY);

        return array_combine(
            array_map(fn (string $key) => (string) str_replace($prefix, '', $key), array_keys($filtered)),
            array_values($filtered)
        );
    }

    private function getRealValuesFromObscured(array $params): array
    {
        if (!isset($params['ff_export_upload_password']) || $params['ff_export_upload_password'] === self::OBSCURED_VALUE) {
            $params['ff_export_upload_password'] = $this->ftpConfig->getUserPassword();
        }

        if (!isset($params['ff_export_upload_key_passphrase']) || $params['ff_export_upload_key_passphrase'] === self::OBSCURED_VALUE) {
            $params['ff_export_upload_key_passphrase'] = $this->ftpConfig->getKeyPassphrase();
        }

        return $params;
    }
}

<?php

declare(strict_types=1);

namespace Factfinder\Export\Cron;

use Factfinder\Export\Model\Api\PushImport;
use Factfinder\Export\Model\Config\CommunicationConfig;
use Factfinder\Export\Model\Export\FeedFactory as FeedGeneratorFactory;
use Factfinder\Export\Model\FtpUploader;
use Factfinder\Export\Model\StoreEmulation;
use Factfinder\Export\Model\Stream\Csv;
use Factfinder\Export\Service\FeedFileService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;

class Feed
{
    private const PATH_CONFIGURABLE_CRON_IS_ENABLED = 'factfinder_export/configurable_cron/ff_export_cron_enabled';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly FeedGeneratorFactory $feedGeneratorFactory,
        private readonly StoreEmulation $storeEmulation,
        private readonly FtpUploader $ftpUploader,
        private readonly CommunicationConfig $communicationConfig,
        private readonly PushImport $pushImport,
        private readonly FeedFileService $feedFileService,
        private readonly Filesystem $filesystem,
        private readonly string $feedType,
    ) {
    }

    public function execute(): void
    {
        if (!$this->scopeConfig->isSetFlag(self::PATH_CONFIGURABLE_CRON_IS_ENABLED)) {
            return;
        }

        foreach ($this->storeManager->getStores() as $store) {
            $this->storeEmulation->runInStore((int) $store->getId(), function () use ($store) {
                $storeId = (int) $store->getId();

                if ($this->communicationConfig->isChannelEnabled($storeId)) {
                    $filename = $this->feedFileService->getFeedExportFilename(
                        $this->feedType,
                        $this->communicationConfig->getChannel()
                    );
                    $stream = new Csv($this->filesystem, "factfinder/$filename");
                    $this->feedGeneratorFactory->create($this->feedType)->generate($stream);
                    $this->ftpUploader->upload($filename, $stream);

                    if ($this->communicationConfig->isPushImportEnabled($storeId)) {
                        $this->pushImport->execute($storeId);
                    }
                }
            });
        }
    }
}

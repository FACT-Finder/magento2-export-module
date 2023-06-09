<?php

declare(strict_types=1);

namespace Factfinder\Export\Controller\Export;

use Factfinder\Export\Model\Config\CommunicationConfig;
use Factfinder\Export\Model\Export\FeedFactory as FeedGeneratorFactory;
use Factfinder\Export\Model\StoreEmulation;
use Factfinder\Export\Model\Stream\Csv;
use Factfinder\Export\Service\FeedFileService;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;

class Product extends Action
{
    private const FEED_TYPE = 'product';

    public function __construct(
        Context $context,
        private readonly CommunicationConfig $communicationConfig,
        private readonly StoreEmulation $storeEmulation,
        private readonly FeedGeneratorFactory $feedGeneratorFactory,
        private readonly FileFactory $fileFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly FeedFileService $feedFileService,
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $storeId = (int) $this->getRequest()->getParam('store', $this->storeManager->getDefaultStoreView()->getId());
        $this->storeEmulation->runInStore($storeId, function () {
            $filename = $this->feedFileService->getFeedExportFilename(self::FEED_TYPE, $this->communicationConfig->getChannel());
            $stream = new Csv($this->filesystem, "factfinder/$filename");
            $this->feedGeneratorFactory->create(self::FEED_TYPE)->generate($stream);
            $this->fileFactory->create($filename, $stream->getContent());
        });
    }
}

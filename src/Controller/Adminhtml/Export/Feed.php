<?php

declare(strict_types=1);

namespace Factfinder\Export\Controller\Adminhtml\Export;

use Factfinder\Export\Model\Api\PushImport;
use Factfinder\Export\Model\Config\CommunicationConfig;
use Factfinder\Export\Model\Export\FeedFactory as FeedGeneratorFactory;
use Factfinder\Export\Model\FtpUploader;
use Factfinder\Export\Model\StoreEmulation;
use Factfinder\Export\Model\Stream\Csv;
use Factfinder\Export\Service\FeedFileService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;

class Feed extends Action
{
    private const FEED_TYPE = 'product';

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonResultFactory,
        private readonly CommunicationConfig $communicationConfig,
        private readonly StoreEmulation $storeEmulation,
        private readonly FeedGeneratorFactory $feedGeneratorFactory,
        private readonly FtpUploader $ftpUploader,
        private readonly PushImport $pushImport,
        private readonly StoreManagerInterface $storeManager,
        private readonly FeedFileService $feedFileService,
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();

        try {
            //@phpcs:ignore Magento2.Legacy.ObsoleteResponse.RedirectResponseMethodFound
            preg_match('@/store/([0-9]+)/@', (string) $this->_redirect->getRefererUrl(), $match);
            $storeId = (int) ($match[1] ?? $this->storeManager->getDefaultStoreView()->getId());
            $messages = [];
            $this->storeEmulation->runInStore($storeId, function () use ($storeId, &$messages, $result) {
                $channel = $this->communicationConfig->getChannel();

                if (!$this->communicationConfig->isChannelEnabled($storeId)) {
                    $message = sprintf('Integration for the channel `%s` is not enabled', $channel);
                    $result->setData(['message' => $message]);

                    return $result;
                }

                $filename = $this->feedFileService->getFeedExportFilename(self::FEED_TYPE, $channel);
                $stream = new Csv($this->filesystem, "factfinder/$filename");
                $path = $this->feedFileService->getExportPath($filename);
                $this->feedGeneratorFactory->create(self::FEED_TYPE)->generate($stream);
                $messages[] = __('<li>Feed file for channel %1 has been generated under %2</li>', $channel, $path);

                try {
                    $this->ftpUploader->upload($filename, $stream);
                    $messages[] = __('<li>File was uploaded to the FTP server.</li>');

                    if ($this->communicationConfig->isPushImportEnabled($storeId)) {
                        try {
                            $this->pushImport->execute($storeId);
                            $result = $this->pushImport->getPushImportResult();
                            $messages[] = __('<li>Push import result</li><ul>' . $result . '</ul>');
                        } catch (\Exception $e) {
                            $messages[] = __('<li>Push import failed.</li>');
                        }
                    }
                } catch (\Exception $e) {
                    $messages[] = __('<li>Error while uploading file to the FTP.</li><li>Push import was not started.</li>');
                }
            });

            $message = sprintf('<ul>%s</ul>', implode('', $messages));

            $result->setData(['message' => $message]);
        } catch (\Exception $e) {
            $result->setData(['message' => $e->getMessage()]);
        }

        return $result;
    }
}

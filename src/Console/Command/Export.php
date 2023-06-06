<?php

declare(strict_types=1);

namespace Factfinder\Export\Console\Command;

use Factfinder\Export\Model\Api\PushImport;
use Factfinder\Export\Model\Config\CommunicationConfig;
use Factfinder\Export\Model\Export\FeedFactory as FeedGeneratorFactory;
use Factfinder\Export\Model\FtpUploader;
use Factfinder\Export\Model\StoreEmulation;
use Factfinder\Export\Model\Stream\Csv;
use Factfinder\Export\Service\FeedFileService;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Export extends Command
{

    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly FeedGeneratorFactory $feedGeneratorFactory,
        private readonly StoreEmulation $storeEmulation,
        private readonly FtpUploader $ftpUploader,
        private readonly CommunicationConfig $communicationConfig,
        private readonly PushImport $pushImport,
        private readonly State $state,
        private readonly FeedFileService $feedFileService,
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setName('factfinderexport:export');
        $this->setDescription('Export feed data as CSV file');
        $this->addArgument('type', InputArgument::REQUIRED, 'type of data to be exported. Possible values are : product, cms');
        $this->addOption('store', 's', InputOption::VALUE_OPTIONAL, 'Store ID or Store Code');
        $this->addOption('upload', 'u', InputOption::VALUE_NONE, 'Upload feed via FTP');
        $this->addOption('push-import', 'i', InputOption::VALUE_NONE, 'Push Import');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode('frontend');
        $storeIds = $this->getStoreIds((int) $input->getOption('store'));
        $type = $input->getArgument('type');

        if (count($storeIds) === 0) {
            $output->writeln('There is no integration enabled for any store');

            return Command::SUCCESS;
        }

        foreach ($storeIds as $storeId) {
            $this->storeEmulation->runInStore($storeId, function () use ($storeId, $input, $output, $type) {
                $filename = $this->feedFileService->getFeedExportFilename($type, $this->communicationConfig->getChannel($storeId));
                $stream = new Csv($this->filesystem, "factfinder/$filename");
                $path = $this->feedFileService->getExportPath($filename);

                $this->feedGeneratorFactory->create($type)->generate($stream);
                $output->writeln("Store {$storeId}: File {$path} has been generated.");

                if ($input->getOption('upload')) {
                    $this->ftpUploader->upload($filename, $stream);
                    $output->writeln("Store {$storeId}: File {$filename} has been uploaded to FTP.");
                }

                if ($input->getOption('push-import') && $this->pushImport->execute((int) $storeId)) {
                    $output->writeln("Store {$storeId}: Push Import for File {$filename} has been triggered.");
                }
            });
        }

        return Command::SUCCESS;
    }

    private function getStoreIds(int $storeId): array
    {
        $storeIds = array_map(
            fn ($store) => (int) $store->getId(),
            $storeId ? [$this->storeManager->getStore($storeId)] : $this->storeManager->getStores()
        );

        return array_filter($storeIds, [$this->communicationConfig, 'isChannelEnabled']);
    }
}

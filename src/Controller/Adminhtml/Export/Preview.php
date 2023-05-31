<?php

declare(strict_types=1);

namespace Factfinder\Export\Controller\Adminhtml\Export;

use Factfinder\Export\Api\StreamInterface;
use Factfinder\Export\Model\Export\FeedFactory;
use Factfinder\Export\Model\Stream\Json as JsonStream;
use Factfinder\Export\Utilities\Validator\ExportPreviewValidator;
use Magento\Backend\App\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Preview extends Action
{
    private RequestInterface $request;

    public function __construct(
        Action\Context $context,
        private readonly JsonFactory $jsonResultFactory,
        private readonly FeedFactory $feedGeneratorFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ConfigurableType $configurableType
    ) {
        parent::__construct($context);
        $this->request = $context->getRequest();
    }

    public function execute(): Json
    {
        $response = $this->jsonResultFactory->create();

        try {
            $entityId = (int) $this->request->getParam('entityId', 0);
            (new ExportPreviewValidator($this->productRepository, $this->configurableType, $entityId))->validate();

            return $response->setData($this->getExportData($entityId));
        } catch (\Throwable $e) {
            return $response->setData(['message' => $e->getMessage()]);
        }
    }

    public function getExportData(int $entityId): array
    {
        $feedType = 'exportPreviewProduct';
        $stream = new JsonStream();
        $this->feedGeneratorFactory->create($feedType, ['entityId' => $entityId])->generate($stream);
        $items = $this->getItems($stream);

        return [
            'totalRecords' => count($items),
            'items' => $items,
        ];
    }

    private function getItems(StreamInterface $stream): array
    {
        $content = json_decode($stream->getContent(), true);

        return array_splice($content, 1, count($content));
    }
}

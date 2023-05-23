<?php

declare(strict_types=1);

namespace Factfinder\Export\Controller\Adminhtml\TestConnection;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class TestApiConnection extends Action
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setContents('Hello admin ajax');

        return $result;
    }
}
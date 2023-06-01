<?php

declare(strict_types=1);

namespace Factfinder\Export\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;

class ExportPreview extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData(): array
    {
        $url = $this->getUrl(
            'factfinder-export/export/preview',
            ['entityId' => (int) $this->context->getRequestParam('id', 0)]
        );

        return [
            'label' => __('Product Export Preview'),
            'class' => 'action-secondary',
            'sort_order' => 25,
            'on_click' => sprintf("window.open('%s', '_blank');", $url),
        ];
    }
}

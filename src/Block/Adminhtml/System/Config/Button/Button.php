<?php

declare(strict_types=1);

namespace Factfinder\Export\Block\Adminhtml\System\Config\Button;

use Magento\Backend\Block\Widget\Button as ButtonWidget;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

abstract class Button extends Field
{
    public function render(AbstractElement $element): string
    {
        $element->unsetData(['scope', 'can_use_website_value', 'can_use_default_value']);

        return parent::render($element);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element): string // phpcs:ignore
    {
        /** @var ButtonWidget $button */
        $button = $this->getLayout()->createBlock(ButtonWidget::class);
        $button->setData([
            'label'          => $this->getLabel(),
            'data_attribute' => [
                'mage-init' => ['Factfinder_Export/js/ajax-button' => ['url' => $this->getTargetUrl()]],
            ],
        ]);

        return $button->toHtml();
    }

    abstract protected function getLabel(): string;
    abstract protected function getTargetUrl(): string;
}

<?php

declare(strict_types=1);

namespace Factfinder\Export\Block\Adminhtml\System\Config\Button;

class TestApiConnection extends Button
{
    protected function getLabel(): string
    {
        return (string) __('Test Connection');
    }

    protected function getTargetUrl(): string
    {
        return $this->getUrl('factfinder-export/testconnection/testapiconnection');
    }
}

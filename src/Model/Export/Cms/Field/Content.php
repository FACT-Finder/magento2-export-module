<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Cms\Field;

use Factfinder\Export\Api\Export\FieldInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Email\Model\Template\Filter;
use Magento\Framework\Model\AbstractModel;

class Content implements FieldInterface
{
    public function __construct(private readonly Filter $filter)
    {
    }

    public function getName(): string
    {
        return 'Content';
    }

    /**
     * @param PageInterface $page
     *
     * @return string
     */
    public function getValue(AbstractModel $page): string
    {
        $filteredContent  = $this->filter->filter($page->getContent());
        $stylesAndScripts = '#\<(?:style|script)[^\>]*\>[^\<]*\</(?:style|script)\>#siU';
        $variables = '#{{[^}]*}}#siU';
        $returns = '#<br\s?\/?>#';
        $whitespaces = '#(\s|&nbsp;)+#s';

        return preg_replace([$stylesAndScripts, $variables, $returns, $whitespaces], ' ', $filteredContent);
    }
}

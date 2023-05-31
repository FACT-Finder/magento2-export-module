<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Catalog\ProductField;

use Factfinder\Export\Api\Export\FieldInterface;
use Factfinder\Export\Model\Formatter\CategoryPathFormatter;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractModel;

class CategoryPath implements FieldInterface
{
    private CategoryPathFormatter $categoryPathFormatter;
    private string $fieldName;

    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        string $fieldName = 'CategoryPath',
    ) {
        $this->fieldName = $fieldName;
        $this->categoryPathFormatter = new CategoryPathFormatter($this->categoryRepository);
    }

    public function getName(): string
    {
        return $this->fieldName;
    }

    /**
     * @param Product $product
     */
    public function getValue(AbstractModel $product): string
    {
        $paths = array_map(function (int $categoryId) use ($product): string {
            return $this->categoryPathFormatter->format($categoryId, $product->getStore());
        }, $product->getCategoryIds());

        return implode('|', array_filter($paths));
    }
}

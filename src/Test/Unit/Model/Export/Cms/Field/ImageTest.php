<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export\Cms\Field;

use Magento\Email\Model\Template\Filter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Model\AbstractModel;

/**
 * @covers Image
 */
class ImageTest extends TestCase
{
    /** @var MockObject|Filter */
    private $filterMock;

    /** @var Image */
    private $imageField;

    /** @var string */
    private $content = 'Some Cms page with Image <img scr="http://magento-test.factfinder.de/media/image/cms.png" />';

    public function test_image_url_should_be_extracted()
    {
        $pageMock = $this->getMockBuilder(AbstractModel::class)
                         ->disableOriginalConstructor()
                         ->addMethods(['getContent'])
                         ->getMock();
        $pageMock->method('getContent')->willReturn($this->content);
        $imageUrl = $this->imageField->getValue($pageMock);
        $this->assertSame('http://magento-test.factfinder.de/media/image/cms.png', $imageUrl);
    }

    protected function setUp(): void
    {
        $this->filterMock = $this->createConfiguredMock(Filter::class, ['filter' => $this->content]);
        $this->imageField = new Image($this->filterMock);
    }
}

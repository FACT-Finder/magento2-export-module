<?php

declare(strict_types=1);

namespace Factfinder\Export\Observer;

use Factfinder\Export\Model\Export\BasicAuth;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Event\Observer;
use Magento\Framework\HTTP\Authentication;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers ExportAuthentication
 */
class ExportAuthenticationTest extends TestCase
{
    private ExportAuthentication $observer;
    private MockObject|ActionFlag $flagMock;
    private MockObject|Authentication $authMock;

    protected function setUp(): void
    {
        $this->authMock = $this->createMock(BasicAuth::class);
        $this->flagMock = $this->createMock(ActionFlag::class);
        $credentials = $this->createMock(Authentication::class);
        $credentials->method('getCredentials')->willReturn(['Aladdin', 'OpenSesame']);
        $this->observer = new ExportAuthentication($this->flagMock, $this->authMock, $credentials);
    }

    public function testShouldChecksUserAuthenticationBeforeDispatch()
    {
        $this->authMock->method('authenticate')->willReturn(true);
        $this->flagMock->expects($this->never())->method('set');
        $this->observer->execute(new Observer());
    }

    public function testShouldPreventsDispatchIfUserIsNotAuthorized()
    {
        $this->authMock->method('authenticate')->willReturn(false);
        $this->flagMock->expects($this->once())->method('set')->with('', 'no-dispatch', true);
        $this->observer->execute(new Observer());
    }
}

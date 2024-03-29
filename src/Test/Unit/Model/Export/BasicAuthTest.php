<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Export;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers BasicAuth
 */
class BasicAuthTest extends TestCase
{
    private BasicAuth $basicAuth;

    protected function setUp(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnMap([
            ['factfinder_export/basic_auth/ff_export_auth_user', 'store', null, 'Aladdin'],
            ['factfinder_export/basic_auth/ff_export_auth_password', 'store', null, 'OpenSesame'],
        ]);
        $this->basicAuth = new BasicAuth($scopeConfig);
    }

    public function testShouldAuthenticatesUserWithValidCredentials()
    {
        $this->assertFalse(
            $this->basicAuth->authenticate('UnknownUser', 'OpenSesame'),
            'User should not be authenticated with a wrong username.'
        );
        $this->assertFalse(
            $this->basicAuth->authenticate('Aladdin', 'WrongPassword'),
            'User should not be authenticated with a wrong password.'
        );
        $this->assertFalse(
            $this->basicAuth->authenticate('UnknownUser', 'WrongPassword'),
            'User should not be authenticated with wrong credentials.'
        );
        $this->assertTrue(
            $this->basicAuth->authenticate('Aladdin', 'OpenSesame'),
            'User should be authenticated with correct credentials.'
        );
    }
}

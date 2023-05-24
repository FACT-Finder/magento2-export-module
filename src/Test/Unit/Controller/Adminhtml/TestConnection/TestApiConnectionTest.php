<?php

declare(strict_types=1);

namespace Factfinder\Export\Controller\Adminhtml\TestConnection;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Omikron\FactFinder\Communication\Client\ClientBuilder;
use Omikron\FactFinder\Communication\Client\ClientInterface;
use Omikron\FactFinder\Communication\Credentials;
use Factfinder\Export\Model\Api\CredentialsFactory;
use Factfinder\Export\Model\Config\AuthConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers TestApiConnection
 */
class TestApiConnectionTest extends TestCase
{
    private TestApiConnection $controller;

    /** @var MockObject */
    private MockObject $request;

    public function test_prevent_errors_without_post_data()
    {
        $this->request->method('getParams')->willReturn([]);
        $this->request->method('getParam')->willReturnMap([
            ['address', null, 'https://fake-factfinder.de/fact-finder'],
            ['version', null, 'ng'],
            ['ff_api_version', null, 'v5'],
            ['channel', null, 'foo'],
        ]);

        $result = $this->controller->execute();
        $this->assertNull($result);
    }

    protected function setUp(): void
    {
        $credentialsFactory = $this->createConfiguredMock(
            CredentialsFactory::class,
            ['create' => $this->createMock(Credentials::class)]
        );
        $this->request = $this->createMock(RequestInterface::class);
        $body = $this->createConfiguredMock(StreamInterface::class, ['getContents' => '{"status":"200"}']);
        $clientMock = $this->createConfiguredMock(
            ClientInterface::class,
            ['request' => $this->createConfiguredMock(ResponseInterface::class, ['getBody' => $body])]
        );
        $this->builderMock = $this->createMock(ClientBuilder::class);

        $this->builderMock->method('withVersion')->willReturn($this->builderMock);
        $this->builderMock->method('withServerUrl')->willReturn($this->builderMock);
        $this->builderMock->method('withCredentials')->willReturn($this->builderMock);
        $this->builderMock->method('build')->willReturn($clientMock);

        $this->controller = new TestApiConnection(
            $this->createConfiguredMock(Context::class, ['getRequest' => $this->request]),
            $this->createConfiguredMock(JsonFactory::class, ['create' => $this->createMock(Json::class)]),
            $credentialsFactory,
            $this->createMock(AuthConfig::class),
            $this->builderMock
        );
    }
}

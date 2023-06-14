<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Api;

use Factfinder\Export\Model\Config\CommunicationConfig;
use Factfinder\Export\Model\Config\ExportConfig;
use Factfinder\Export\Test\ConsecutiveParams;
use Omikron\FactFinder\Communication\Client\ClientBuilder;
use Omikron\FactFinder\Communication\Client\ClientInterface;
use Omikron\FactFinder\Communication\Credentials;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers PushImport
 */
class PushImportTest extends TestCase
{
    use ConsecutiveParams;

    private PushImport $pushImport;
    private MockObject|ClientInterface $factFinderClientMock;
    private MockObject|CommunicationConfig $communicationConfigMock;
    private MockObject|ExportConfig $exportConfigMock;
    private MockObject|ClientBuilder $builderMock;
    private MockObject|ClientInterface $clientMock;

    protected function setUp(): void
    {
        $this->communicationConfigMock = $this->createMock(CommunicationConfig::class);
        $this->communicationConfigMock->method('getAddress')->willReturn('http://fake-factfinder.com/FACT-Finder-7.3');
        $this->communicationConfigMock->method('getVersion')->willReturn('ng');
        $this->communicationConfigMock->method('getApiVersion')->willReturn('v5');
        $this->exportConfigMock = $this->createMock(ExportConfig::class);
        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->builderMock = $this->createMock(ClientBuilder::class);
        $this->builderMock->method('withVersion')->willReturn($this->builderMock);
        $this->builderMock->method('withServerUrl')->willReturn($this->builderMock);
        $this->builderMock->method('withCredentials')->willReturn($this->builderMock);
        $this->builderMock->method('build')->willReturn($this->clientMock);
        $this->pushImport = new PushImport(
            $this->builderMock,
            $this->createConfiguredMock(
                CredentialsFactory::class,
                ['create' => $this->createMock(Credentials::class)]
            ),
            $this->communicationConfigMock,
            $this->exportConfigMock,
        );
    }

    public function testShouldThrowIfImportIsRunning()
    {
        $this->exportConfigMock->method('getPushImportDataTypes')->with($this->anything())->willReturn(['search','suggest']);
        $this->clientMock->method('request')->with('GET', 'rest/v5/import/running', $this->anything())
            ->willReturn($this->importRunningResponse());
        $this->expectExceptionMessage("Can't start a new import process. Another one is still going");
        $this->pushImport->execute(1);
    }

    public function testShouldNotTriggerImportIfNoDataTypeIsConfigured()
    {
        $this->exportConfigMock->method('getPushImportDataTypes')->with($this->anything())->willReturn([]);
        $this->clientMock->expects($this->never())->method('request');
        $this->assertFalse($this->pushImport->execute(1));
    }

    private function importRunningResponse(): ResponseInterface
    {
        $body = $this->createConfiguredMock(StreamInterface::class, ['__toString' => 'true']);

        return $this->createConfiguredMock(ResponseInterface::class, ['getBody' => $body]);
    }
}

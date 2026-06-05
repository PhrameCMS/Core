<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Contracts\HttpTransportInterface;
use PhrameCMS\Core\Http\HttpTransportFactory;
use PhrameCMS\Core\Http\NativeHttpTransport;
use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Http\Response;

final class HttpTransportFactoryTest extends TestCase
{
    private string|false $original;

    protected function setUp(): void
    {
        $this->original = getenv('PHRAME_HTTP_TRANSPORT');
    }

    protected function tearDown(): void
    {
        if ($this->original === false) {
            putenv('PHRAME_HTTP_TRANSPORT');
        } else {
            putenv('PHRAME_HTTP_TRANSPORT=' . $this->original);
        }
    }

    public function testConfiguredNativeReturnsNativeTransport(): void
    {
        putenv('PHRAME_HTTP_TRANSPORT=native');

        self::assertInstanceOf(NativeHttpTransport::class, HttpTransportFactory::createDefault());
    }

    public function testConfiguredSymfonyUsesBridgeWhenAvailableOrFallsBack(): void
    {
        putenv('PHRAME_HTTP_TRANSPORT=symfony');

        $transport = HttpTransportFactory::createDefault();

        if (self::isSymfonyBridgeInstalled()) {
            self::assertContains($transport::class, [
                'PhrameCMS\\HttpFoundationBridge\\HttpFoundationBridge',
                'PhrameCMS\\Core\\Http\\HttpFoundationBridge',
            ]);

            return;
        }

        self::assertInstanceOf(NativeHttpTransport::class, $transport);
    }

    public function testInvalidClassThrows(): void
    {
        putenv('PHRAME_HTTP_TRANSPORT=Nope\\Missing');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('class was not found');

        HttpTransportFactory::createDefault();
    }

    public function testCustomTransportMustImplementContract(): void
    {
        putenv('PHRAME_HTTP_TRANSPORT=InvalidTransportClassForFactoryTest');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must implement');

        HttpTransportFactory::createDefault();
    }

    public function testCustomTransportClassIsSupported(): void
    {
        putenv('PHRAME_HTTP_TRANSPORT=CustomTransportForFactoryTest');

        self::assertInstanceOf(CustomTransportForFactoryTest::class, HttpTransportFactory::createDefault());
    }

    private static function isSymfonyBridgeInstalled(): bool
    {
        $packageBridge = 'PhrameCMS\\HttpFoundationBridge\\HttpFoundationBridge';
        if (class_exists($packageBridge) && method_exists($packageBridge, 'isAvailable')) {
            return $packageBridge::isAvailable();
        }

        $coreBridge = 'PhrameCMS\\Core\\Http\\HttpFoundationBridge';
        if (class_exists($coreBridge) && method_exists($coreBridge, 'isAvailable')) {
            return $coreBridge::isAvailable();
        }

        return false;
    }
}

final class CustomTransportForFactoryTest implements HttpTransportInterface
{
    public function captureRequest(): Request
    {
        return new Request(\PhrameCMS\Core\Http\HttpMethod::GET, '/', [], [], null);
    }

    public function emitResponse(Response $response): void
    {
    }
}

final class InvalidTransportClassForFactoryTest
{
}

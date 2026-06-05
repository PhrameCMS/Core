<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Http\HttpMethod;
use PhrameCMS\Core\Http\NativeHttpTransport;
use PhrameCMS\Core\Http\Response;

final class NativeHttpTransportTest extends TestCase
{
    /** @var array<mixed, mixed> */
    private array $serverBackup;

    /** @var array<mixed, mixed> */
    private array $getBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->getBackup = $_GET;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_GET = $this->getBackup;
    }

    public function testCaptureRequestUsesGlobals(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/transport';
        $_GET = [];

        $transport = new NativeHttpTransport();
        $request = $transport->captureRequest();

        self::assertSame(HttpMethod::GET, $request->method);
        self::assertSame('/transport', $request->path);
    }

    public function testEmitResponseDoesNotThrow(): void
    {
        $transport = new NativeHttpTransport();

        ob_start();
        $transport->emitResponse(new Response(200, 'ok'));

        self::assertSame('ok', (string) ob_get_clean());
    }
}

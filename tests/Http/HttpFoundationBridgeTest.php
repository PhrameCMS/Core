<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Http\HttpFoundationBridge;
use PhrameCMS\Core\Http\Request;

final class HttpFoundationBridgeTest extends TestCase
{
    private array $serverBackup;
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

    public function testRequestFromGlobalsReturnsCoreRequestWhenAvailable(): void
    {
        if (!HttpFoundationBridge::isAvailable()) {
            self::markTestSkipped('HttpFoundation is unavailable in this environment.');
        }

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/bridge';
        $_GET = ['x' => '1'];

        $request = HttpFoundationBridge::requestFromGlobals();

        self::assertInstanceOf(Request::class, $request);
        self::assertSame('/bridge', $request->path);
        self::assertSame(['x' => '1'], $request->query);
    }
}

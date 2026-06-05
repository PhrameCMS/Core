<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Http\HttpMethod;
use PhrameCMS\Core\Http\Request;

final class RequestTest extends TestCase
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

    public function testFromGlobalsBuildsRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['REQUEST_URI'] = '/items/list?page=2';
        $_SERVER['HTTP_X_TOKEN'] = 'abc123';
        $_GET = ['page' => '2'];

        $request = Request::fromGlobals();

        self::assertSame(HttpMethod::POST, $request->method);
        self::assertSame('/items/list', $request->path);
        self::assertSame(['page' => '2'], $request->query);
        self::assertSame('abc123', $request->headers['x-token'] ?? null);
        self::assertNull($request->jsonBody);
    }
}

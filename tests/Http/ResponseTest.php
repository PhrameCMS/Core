<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Http\Response;

final class ResponseTest extends TestCase
{
    public function testJsonFactoryCreatesJsonResponse(): void
    {
        $response = Response::json(['ok' => true], 201);

        self::assertSame(201, $response->status());
        self::assertSame(['Content-Type' => 'application/json; charset=utf-8'], $response->headers());
        self::assertStringContainsString('"ok": true', $response->body());
    }

    public function testHtmlFactoryCreatesHtmlResponse(): void
    {
        $response = Response::html('<h1>Hello</h1>', 202);

        self::assertSame(202, $response->status());
        self::assertSame(['Content-Type' => 'text/html; charset=utf-8'], $response->headers());
        self::assertSame('<h1>Hello</h1>', $response->body());
    }
}

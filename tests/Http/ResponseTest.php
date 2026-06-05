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
}

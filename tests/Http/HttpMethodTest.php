<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Http\HttpMethod;

final class HttpMethodTest extends TestCase
{
    public function testFromStringNormalizesAndFallsBackToGet(): void
    {
        self::assertSame(HttpMethod::POST, HttpMethod::fromString('post'));
        self::assertSame(HttpMethod::GET, HttpMethod::fromString('invalid'));
    }
}

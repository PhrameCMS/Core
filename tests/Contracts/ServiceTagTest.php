<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Contracts\ServiceTag;

final class ServiceTagTest extends TestCase
{
    public function testRouteProviderValueIsStable(): void
    {
        self::assertSame('route.provider', ServiceTag::RouteProvider->value);
    }
}

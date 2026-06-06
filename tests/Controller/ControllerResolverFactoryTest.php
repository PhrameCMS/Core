<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use PhrameCMS\Core\Contracts\ControllerResolverInterface;
use PhrameCMS\Core\Controller\ControllerResolverFactory;
use PhrameCMS\Core\Controller\NativeControllerResolver;
use PhrameCMS\Core\Http\Response;

final class ControllerResolverFactoryTest extends TestCase
{
    private string|false $original;

    protected function setUp(): void
    {
        $this->original = getenv('PHRAME_CONTROLLER_RESOLVER');
    }

    protected function tearDown(): void
    {
        if ($this->original === false) {
            putenv('PHRAME_CONTROLLER_RESOLVER');
        } else {
            putenv('PHRAME_CONTROLLER_RESOLVER=' . $this->original);
        }
    }

    public function testConfiguredNativeReturnsNativeControllerResolver(): void
    {
        putenv('PHRAME_CONTROLLER_RESOLVER=native');

        self::assertInstanceOf(NativeControllerResolver::class, ControllerResolverFactory::createDefault());
    }

    public function testInvalidConfiguredClassThrows(): void
    {
        putenv('PHRAME_CONTROLLER_RESOLVER=Nope\\MissingResolver');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('class was not found');

        ControllerResolverFactory::createDefault();
    }

    public function testCustomResolverClassIsSupported(): void
    {
        putenv('PHRAME_CONTROLLER_RESOLVER=CustomResolverForFactoryTest');

        self::assertInstanceOf(CustomResolverForFactoryTest::class, ControllerResolverFactory::createDefault());
    }
}

final class CustomResolverForFactoryTest implements ControllerResolverInterface
{
    public function resolve(string $controllerReference, ContainerBuilderInterface $container): callable
    {
        return static fn (): Response => Response::json(['ok' => true]);
    }
}

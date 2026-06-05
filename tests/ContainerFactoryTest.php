<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\ContainerFactory;
use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use PhrameCMS\Core\CoreContainer;
use PhrameCMS\Core\Contracts\ServiceTag;
use PhrameCMS\Core\SymfonyContainer;

final class ContainerFactoryTest extends TestCase
{
    private string|false $original;

    protected function setUp(): void
    {
        $this->original = getenv('PHRAME_CONTAINER');
    }

    protected function tearDown(): void
    {
        if ($this->original === false) {
            putenv('PHRAME_CONTAINER');
        } else {
            putenv('PHRAME_CONTAINER=' . $this->original);
        }
    }

    public function testConfiguredNativeReturnsCoreContainer(): void
    {
        putenv('PHRAME_CONTAINER=native');

        $container = ContainerFactory::createDefault();

        self::assertInstanceOf(CoreContainer::class, $container);
    }

    public function testConfiguredSymfonyIsStrict(): void
    {
        putenv('PHRAME_CONTAINER=symfony');

        if (SymfonyContainer::isAvailable()) {
            self::assertInstanceOf(SymfonyContainer::class, ContainerFactory::createDefault());

            return;
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid PHRAME_CONTAINER "symfony"');

        ContainerFactory::createDefault();
    }

    public function testInvalidConfiguredClassThrows(): void
    {
        putenv('PHRAME_CONTAINER=Nope\\Missing');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('class was not found');

        ContainerFactory::createDefault();
    }

    public function testCustomContainerClassMustImplementContract(): void
    {
        putenv('PHRAME_CONTAINER=InvalidContainerClassForFactoryTest');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must implement');

        ContainerFactory::createDefault();
    }

    public function testCustomContainerClassIsSupported(): void
    {
        putenv('PHRAME_CONTAINER=CustomContainerForFactoryTest');

        $container = ContainerFactory::createDefault();

        self::assertInstanceOf(CustomContainerForFactoryTest::class, $container);
    }
}

final class CustomContainerForFactoryTest implements ContainerBuilderInterface
{
    public function get(string $id): mixed
    {
        return null;
    }

    public function has(string $id): bool
    {
        return false;
    }

    public function set(string $id, mixed $concrete, bool $shared = true): void
    {
    }

    public function tag(ServiceTag|string $tag, string $serviceId): void
    {
    }

    public function tagged(ServiceTag|string $tag): array
    {
        return [];
    }
}

final class InvalidContainerClassForFactoryTest
{
}

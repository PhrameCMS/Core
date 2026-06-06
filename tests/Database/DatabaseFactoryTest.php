<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Contracts\DatabaseAdapterInterface;
use PhrameCMS\Core\Database\DatabaseFactory;

final class DatabaseFactoryTest extends TestCase
{
    private string|false $original;

    protected function setUp(): void
    {
        $this->original = getenv('PHRAME_DATABASE');
    }

    protected function tearDown(): void
    {
        if ($this->original === false) {
            putenv('PHRAME_DATABASE');
        } else {
            putenv('PHRAME_DATABASE=' . $this->original);
        }
    }

    public function testConfiguredNoneReturnsNull(): void
    {
        putenv('PHRAME_DATABASE=none');

        self::assertNull(DatabaseFactory::createDefault());
    }

    public function testConfiguredBridgeUsesPreferredAdapterWhenAvailableOrReturnsNull(): void
    {
        putenv('PHRAME_DATABASE=bridge');

        $adapter = DatabaseFactory::createDefault();

        if (self::isPreferredAdapterInstalled()) {
            self::assertNotNull($adapter);
            self::assertContains($adapter::class, [
                'PhrameCMS\\DoctrineDbalBridge\\DoctrineDbalBridge',
            ]);

            return;
        }

        self::assertNull($adapter);
    }

    public function testInvalidClassThrows(): void
    {
        putenv('PHRAME_DATABASE=Nope\\Missing');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('class was not found');

        DatabaseFactory::createDefault();
    }

    public function testCustomAdapterMustImplementContract(): void
    {
        putenv('PHRAME_DATABASE=InvalidDatabaseAdapterForFactoryTest');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must implement');

        DatabaseFactory::createDefault();
    }

    public function testCustomAdapterClassIsSupported(): void
    {
        putenv('PHRAME_DATABASE=CustomDatabaseAdapterForFactoryTest');

        self::assertInstanceOf(CustomDatabaseAdapterForFactoryTest::class, DatabaseFactory::createDefault());
    }

    private static function isPreferredAdapterInstalled(): bool
    {
        $adapterClass = 'PhrameCMS\\DoctrineDbalBridge\\DoctrineDbalBridge';

        if (!class_exists($adapterClass)) {
            return false;
        }

        return (bool) $adapterClass::isAvailable();
    }
}

final class CustomDatabaseAdapterForFactoryTest implements DatabaseAdapterInterface
{
    /**
        * @param array<int|string, mixed> $params
     */
    public function execute(string $sql, array $params = []): int
    {
        return 1;
    }

    /**
        * @param array<int|string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return [];
    }

    /**
        * @param array<int|string, mixed> $params
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        return null;
    }

    public function beginTransaction(): void
    {
    }

    public function commit(): void
    {
    }

    public function rollback(): void
    {
    }
}

final class InvalidDatabaseAdapterForFactoryTest
{
}

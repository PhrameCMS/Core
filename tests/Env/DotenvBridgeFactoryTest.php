<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Contracts\DotenvBridgeInterface;
use PhrameCMS\Core\Env\DotenvBridgeFactory;

final class DotenvBridgeFactoryTest extends TestCase
{
    private string|false $originalBridgeClass;

    protected function setUp(): void
    {
        $this->originalBridgeClass = getenv('PHRAME_DOTENV_BRIDGE');
        putenv('PHRAME_DOTENV_BRIDGE');
        putenv('DOTENV_FACTORY_TEST_KEY');
    }

    protected function tearDown(): void
    {
        if ($this->originalBridgeClass === false) {
            putenv('PHRAME_DOTENV_BRIDGE');
        } else {
            putenv('PHRAME_DOTENV_BRIDGE=' . $this->originalBridgeClass);
        }

        putenv('DOTENV_FACTORY_TEST_KEY');
    }

    public function testConfiguredContractBridgeLoadsEnv(): void
    {
        putenv('PHRAME_DOTENV_BRIDGE=ContractDotenvBridgeForFactoryTest');

        $envFile = tempnam(sys_get_temp_dir(), 'dotenv_factory_');
        self::assertIsString($envFile);
        file_put_contents($envFile, "DOTENV_FACTORY_TEST_KEY=contract_loaded\n");

        try {
            DotenvBridgeFactory::loadEnv($envFile);
            self::assertSame('contract_loaded', getenv('DOTENV_FACTORY_TEST_KEY'));
        } finally {
            @unlink($envFile);
        }
    }

    public function testConfiguredMissingClassThrows(): void
    {
        putenv('PHRAME_DOTENV_BRIDGE=Nope\\MissingDotenvBridge');

        $envFile = tempnam(sys_get_temp_dir(), 'dotenv_factory_');
        self::assertIsString($envFile);
        file_put_contents($envFile, "DOTENV_FACTORY_TEST_KEY=missing\n");

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('class was not found');

            DotenvBridgeFactory::loadEnv($envFile);
        } finally {
            @unlink($envFile);
        }
    }

    public function testConfiguredInvalidClassThrows(): void
    {
        putenv('PHRAME_DOTENV_BRIDGE=InvalidDotenvBridgeForFactoryTest');

        $envFile = tempnam(sys_get_temp_dir(), 'dotenv_factory_');
        self::assertIsString($envFile);
        file_put_contents($envFile, "DOTENV_FACTORY_TEST_KEY=invalid\n");

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('must implement');

            DotenvBridgeFactory::loadEnv($envFile);
        } finally {
            @unlink($envFile);
        }
    }
}

final class ContractDotenvBridgeForFactoryTest implements DotenvBridgeInterface
{
    public function loadEnv(string $envPath): void
    {
        $contents = file_get_contents($envPath);
        if ($contents === false) {
            return;
        }

        foreach (explode("\n", $contents) as $line) {
            if ($line === '' || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            putenv($key . '=' . $value);
        }
    }
}

final class InvalidDotenvBridgeForFactoryTest
{
}

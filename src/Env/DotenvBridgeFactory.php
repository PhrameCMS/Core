<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Env;

use PhrameCMS\Core\Contracts\DotenvBridgeInterface;
use RuntimeException;
use Throwable;

final class DotenvBridgeFactory
{
    private const DEFAULT_BRIDGE_CLASS = 'PhrameCMS\\DotenvBridge\\DotenvBridge';

    public static function loadEnv(string $envPath): void
    {
        if (!is_file($envPath)) {
            return;
        }

        $configured = trim((string) (getenv('PHRAME_DOTENV_BRIDGE') ?: ''));

        if ($configured !== '') {
            self::fromConfiguredClass($configured)->loadEnv($envPath);

            return;
        }

        $bridge = self::fromDefaultClass();
        if ($bridge === null) {
            return;
        }

        $bridge->loadEnv($envPath);
    }

    private static function fromConfiguredClass(string $className): DotenvBridgeInterface
    {
        if (!class_exists($className)) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_DOTENV_BRIDGE "%s": class was not found.',
                $className,
            ));
        }

        $bridge = self::instantiateBridge($className);

        if ($bridge === null) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_DOTENV_BRIDGE "%s": class must implement %s or provide static loadEnv()/isAvailable() methods.',
                $className,
                DotenvBridgeInterface::class,
            ));
        }

        return $bridge;
    }

    private static function fromDefaultClass(): ?DotenvBridgeInterface
    {
        if (!class_exists(self::DEFAULT_BRIDGE_CLASS)) {
            return null;
        }

        return self::instantiateBridge(self::DEFAULT_BRIDGE_CLASS);
    }

    private static function instantiateBridge(string $className): ?DotenvBridgeInterface
    {
        if (is_subclass_of($className, DotenvBridgeInterface::class)) {
            try {
                /** @var DotenvBridgeInterface $bridge */
                $bridge = new $className();

                return $bridge;
            } catch (Throwable $exception) {
                throw new RuntimeException(sprintf(
                    'Invalid PHRAME_DOTENV_BRIDGE "%s": class could not be instantiated (%s).',
                    $className,
                    $exception->getMessage(),
                ), (int) $exception->getCode(), $exception);
            }
        }

        if (method_exists($className, 'loadEnv') && method_exists($className, 'isAvailable')) {
            return new LegacyStaticDotenvBridgeAdapter($className);
        }

        return null;
    }
}

final class LegacyStaticDotenvBridgeAdapter implements DotenvBridgeInterface
{
    /** @var class-string */
    private string $className;

    /**
     * @param class-string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function loadEnv(string $envPath): void
    {
        if (!$this->className::isAvailable()) {
            return;
        }

        $this->className::loadEnv($envPath);
    }
}
<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Http;

use PhrameCMS\Core\Contracts\HttpTransportInterface;
use RuntimeException;
use Throwable;

final class HttpTransportFactory
{
    /**
     * @var list<class-string>
     */
    private const SYMFONY_BRIDGE_CANDIDATES = [
        'PhrameCMS\\HttpFoundationBridge\\HttpFoundationBridge',
        'PhrameCMS\\Core\\Http\\HttpFoundationBridge',
    ];

    public static function createDefault(): HttpTransportInterface
    {
        $configured = trim((string) (getenv('PHRAME_HTTP_TRANSPORT') ?: ''));

        if ($configured !== '') {
            return self::fromConfiguration($configured);
        }

        $transport = self::createSymfonyBridgeTransport();
        if ($transport !== null) {
            return $transport;
        }

        return new NativeHttpTransport();
    }

    private static function fromConfiguration(string $configured): HttpTransportInterface
    {
        $mode = HttpTransportMode::fromConfiguration($configured);

        if ($mode === HttpTransportMode::Native) {
            return new NativeHttpTransport();
        }

        if ($mode === HttpTransportMode::Symfony) {
            $transport = self::createSymfonyBridgeTransport();
            if ($transport !== null) {
                return $transport;
            }

            return new NativeHttpTransport();
        }

        if (!class_exists($configured)) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_HTTP_TRANSPORT "%s": class was not found.',
                $configured,
            ));
        }

        try {
            $transport = new $configured();
        } catch (Throwable $exception) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_HTTP_TRANSPORT "%s": class could not be instantiated (%s).',
                $configured,
                $exception->getMessage(),
            ), (int) $exception->getCode(), $exception);
        }

        if (!$transport instanceof HttpTransportInterface) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_HTTP_TRANSPORT "%s": class must implement %s.',
                $configured,
                HttpTransportInterface::class,
            ));
        }

        return $transport;
    }

    private static function createSymfonyBridgeTransport(): ?HttpTransportInterface
    {
        foreach (self::SYMFONY_BRIDGE_CANDIDATES as $bridgeClass) {
            if (!class_exists($bridgeClass)) {
                continue;
            }

            if (!is_subclass_of($bridgeClass, HttpTransportInterface::class)) {
                continue;
            }

            if (!method_exists($bridgeClass, 'isAvailable')) {
                continue;
            }

            if (!$bridgeClass::isAvailable()) {
                continue;
            }

            return new $bridgeClass();
        }

        return null;
    }
}

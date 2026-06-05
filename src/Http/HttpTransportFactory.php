<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Http;

use PhrameCMS\Core\Contracts\HttpTransportInterface;
use RuntimeException;
use Throwable;

final class HttpTransportFactory
{
    public static function createDefault(): HttpTransportInterface
    {
        $configured = trim((string) (getenv('PHRAME_HTTP_TRANSPORT') ?: ''));

        if ($configured !== '') {
            return self::fromConfiguration($configured);
        }

        if (HttpFoundationBridge::isAvailable()) {
            return new HttpFoundationBridge();
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
            if (HttpFoundationBridge::isAvailable()) {
                return new HttpFoundationBridge();
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
}

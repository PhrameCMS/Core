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
    private const PREFERRED_TRANSPORT_CANDIDATES = [
        'PhrameCMS\\HttpFoundationBridge\\HttpFoundationBridge',
    ];

    public static function createDefault(): HttpTransportInterface
    {
        $configured = trim((string) (getenv('PHRAME_HTTP_TRANSPORT') ?: ''));

        if ($configured !== '') {
            return self::fromConfiguration($configured);
        }

        $transport = self::createPreferredTransport();
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

        if ($mode === HttpTransportMode::Bridge) {
            $transport = self::createPreferredTransport();
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

    private static function createPreferredTransport(): ?HttpTransportInterface
    {
        foreach (self::PREFERRED_TRANSPORT_CANDIDATES as $transportClass) {
            if (!class_exists($transportClass)) {
                continue;
            }

            if (!is_subclass_of($transportClass, HttpTransportInterface::class)) {
                continue;
            }

            if (!method_exists($transportClass, 'isAvailable')) {
                continue;
            }

            if (!$transportClass::isAvailable()) {
                continue;
            }

            return new $transportClass();
        }

        return null;
    }
}

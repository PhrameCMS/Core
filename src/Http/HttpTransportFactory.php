<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Http;

use PhrameCMS\Core\Contracts\HttpTransportInterface;

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
        $normalized = strtolower($configured);

        if ($normalized === 'native') {
            return new NativeHttpTransport();
        }

        if ($normalized === 'symfony' || $normalized === 'httpfoundation' || $normalized === 'http-foundation') {
            if (HttpFoundationBridge::isAvailable()) {
                return new HttpFoundationBridge();
            }

            return new NativeHttpTransport();
        }

        if (class_exists($configured)) {
            $transport = new $configured();
            if ($transport instanceof HttpTransportInterface) {
                return $transport;
            }
        }

        return new NativeHttpTransport();
    }
}

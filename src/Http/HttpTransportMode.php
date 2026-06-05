<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Http;

enum HttpTransportMode: string
{
    case Native = 'native';
    case Symfony = 'symfony';

    public static function fromConfiguration(string $configured): ?self
    {
        $normalized = strtolower(trim($configured));

        if ($normalized === self::Native->value) {
            return self::Native;
        }

        if ($normalized === self::Symfony->value || $normalized === 'httpfoundation' || $normalized === 'http-foundation') {
            return self::Symfony;
        }

        return null;
    }
}

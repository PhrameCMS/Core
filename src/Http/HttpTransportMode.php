<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Http;

enum HttpTransportMode: string
{
    case Native = 'native';
    case Bridge = 'bridge';

    public static function fromConfiguration(string $configured): ?self
    {
        $normalized = strtolower(trim($configured));

        if ($normalized === self::Native->value) {
            return self::Native;
        }

        if (
            $normalized === self::Bridge->value
            || $normalized === 'symfony'
            || $normalized === 'httpfoundation'
            || $normalized === 'http-foundation'
        ) {
            return self::Bridge;
        }

        return null;
    }
}

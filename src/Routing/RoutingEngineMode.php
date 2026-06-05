<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Routing;

enum RoutingEngineMode: string
{
    case Native = 'native';
    case Bridge = 'routing-bridge';

    public static function fromConfiguration(string $configured): ?self
    {
        $normalized = strtolower(trim($configured));

        if ($normalized === self::Native->value) {
            return self::Native;
        }

        if (
            $normalized === self::Bridge->value
            || $normalized === 'symfony-routing'
            || $normalized === 'symfony'
            || $normalized === 'routing'
        ) {
            return self::Bridge;
        }

        return null;
    }
}
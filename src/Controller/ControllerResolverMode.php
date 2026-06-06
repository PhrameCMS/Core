<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Controller;

enum ControllerResolverMode: string
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
            || $normalized === 'controller-bridge'
        ) {
            return self::Bridge;
        }

        return null;
    }
}
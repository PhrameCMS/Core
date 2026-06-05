<?php

declare(strict_types=1);

namespace PhrameCMS\Core;

enum ContainerEngine: string
{
    case Native = 'native';
    case Symfony = 'symfony';

    public static function fromConfiguration(string $configured): ?self
    {
        $normalized = strtolower(trim($configured));

        if ($normalized === self::Native->value) {
            return self::Native;
        }

        if ($normalized === self::Symfony->value || $normalized === 'symfony-di' || $normalized === 'dependency-injection') {
            return self::Symfony;
        }

        return null;
    }
}

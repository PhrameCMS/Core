<?php

declare(strict_types=1);

namespace PhrameCMS\Core;

enum ContainerEngine: string
{
    case Native = 'native';
    case DependencyInjection = 'dependency-injection';

    public static function fromConfiguration(string $configured): ?self
    {
        $normalized = strtolower(trim($configured));

        if ($normalized === self::Native->value) {
            return self::Native;
        }

        if (
            $normalized === self::DependencyInjection->value
            || $normalized === 'symfony'
            || $normalized === 'symfony-di'
        ) {
            return self::DependencyInjection;
        }

        return null;
    }
}

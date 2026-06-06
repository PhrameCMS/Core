<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Database;

enum DatabaseMode: string
{
    case None = 'none';
    case Bridge = 'bridge';

    public static function fromConfiguration(string $configured): ?self
    {
        $normalized = strtolower(trim($configured));

        if (
            $normalized === self::None->value
            || $normalized === 'native'
            || $normalized === 'off'
        ) {
            return self::None;
        }

        if (
            $normalized === self::Bridge->value
            || $normalized === 'doctrine'
            || $normalized === 'dbal'
            || $normalized === 'doctrine-dbal-bridge'
        ) {
            return self::Bridge;
        }

        return null;
    }
}

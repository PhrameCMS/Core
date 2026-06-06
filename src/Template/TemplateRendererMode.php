<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Template;

enum TemplateRendererMode: string
{
    case None = 'none';
    case Bridge = 'bridge';

    public static function fromConfiguration(string $configured): ?self
    {
        $normalized = strtolower(trim($configured));

        if ($normalized === self::None->value || $normalized === 'native' || $normalized === 'off') {
            return self::None;
        }

        if (
            $normalized === self::Bridge->value
            || $normalized === 'twig'
            || $normalized === 'twig-bridge'
        ) {
            return self::Bridge;
        }

        return null;
    }
}
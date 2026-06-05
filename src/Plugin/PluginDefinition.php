<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Plugin;

final class PluginDefinition
{
    /**
     * @param array<int, string> $providers
     * @param array<int, string> $capabilities
     */
    public function __construct(
        public readonly string $package,
        public readonly array $providers,
        public readonly array $capabilities,
    ) {
    }
}

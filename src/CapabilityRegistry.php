<?php

declare(strict_types=1);

namespace Lume\Core;

final class CapabilityRegistry
{
    /**
     * @var array<string, true>
     */
    private array $capabilities = [];

    public function add(string $capability): void
    {
        $normalized = trim($capability);
        if ($normalized === '') {
            return;
        }

        $this->capabilities[$normalized] = true;
    }

    /**
     * @param array<int, string> $capabilities
     */
    public function addMany(array $capabilities): void
    {
        foreach ($capabilities as $capability) {
            $this->add($capability);
        }
    }

    /**
     * @return array<int, string>
     */
    public function all(): array
    {
        $all = array_keys($this->capabilities);
        sort($all);

        return $all;
    }
}

<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Plugin;

final class PluginManager
{
    /**
     * @return array<int, PluginDefinition>
     */
    public function discover(): array
    {
        $installedVersionsClass = 'Composer\\InstalledVersions';
        if (!class_exists($installedVersionsClass) || !method_exists($installedVersionsClass, 'getAllRawData')) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $rawData */
        $rawData = $installedVersionsClass::getAllRawData();

        /** @var array<string, PluginDefinition> $definitionsByPackage */
        $definitionsByPackage = [];

        foreach ($rawData as $dataset) {
            $versions = $dataset['versions'] ?? [];
            if (!is_array($versions)) {
                continue;
            }

            foreach ($versions as $package => $meta) {
                if (!is_array($meta)) {
                    continue;
                }

                $extra = $meta['extra'] ?? null;
                if (!is_array($extra)) {
                    continue;
                }

                $pluginMeta = $extra['phramecms'] ?? $extra['lume'] ?? null;
                if (!is_array($pluginMeta)) {
                    continue;
                }

                $providers = $this->normalizeStringList($pluginMeta['provider'] ?? $pluginMeta['providers'] ?? []);
                $capabilities = $this->normalizeStringList($pluginMeta['capabilities'] ?? []);

                if ($providers === []) {
                    continue;
                }

                $definitionsByPackage[$package] = new PluginDefinition($package, $providers, $capabilities);
            }
        }

        ksort($definitionsByPackage);

        return array_values($definitionsByPackage);
    }

    /**
     * @return array<int, string>
     */
    private function normalizeStringList(mixed $value): array
    {
        if (is_string($value) && trim($value) !== '') {
            return [trim($value)];
        }

        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (!is_string($item)) {
                continue;
            }

            $normalized = trim($item);
            if ($normalized === '') {
                continue;
            }

            $result[$normalized] = $normalized;
        }

        return array_values($result);
    }
}

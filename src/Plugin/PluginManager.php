<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Plugin;

use PhrameCMS\Core\Http\HttpMethod;

final class PluginManager
{
    /**
     * @return array<int, PluginDefinition>
     */
    public function discover(): array
    {
        $installedVersionsClass = 'Composer\\InstalledVersions';
        if (!class_exists($installedVersionsClass)) {
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

                $pluginMeta = $extra['phramecms'] ?? null;
                if (!is_array($pluginMeta)) {
                    continue;
                }

                $providers = $this->normalizeStringList($pluginMeta['provider'] ?? $pluginMeta['providers'] ?? []);
                $capabilities = $this->normalizeStringList($pluginMeta['capabilities'] ?? []);
                $controllers = $this->normalizeControllerList($pluginMeta['controllers'] ?? []);

                if ($providers === []) {
                    continue;
                }

                $definitionsByPackage[$package] = new PluginDefinition($package, $providers, $capabilities, $controllers);
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

    /**
     * @return array<int, ControllerRouteDefinition>
     */
    private function normalizeControllerList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $controllers = [];

        foreach ($value as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $method = isset($entry['method']) && is_string($entry['method'])
                ? trim($entry['method'])
                : '';
            $path = isset($entry['path']) && is_string($entry['path'])
                ? trim($entry['path'])
                : '';
            $controller = isset($entry['controller']) && is_string($entry['controller'])
                ? trim($entry['controller'])
                : '';

            if ($method === '' || $path === '' || $controller === '') {
                continue;
            }

            $httpMethod = HttpMethod::tryFrom(strtoupper($method));
            if ($httpMethod === null) {
                continue;
            }

            $name = isset($entry['name']) && is_string($entry['name']) ? trim($entry['name']) : null;

            $defaults = isset($entry['defaults']) && is_array($entry['defaults']) ? $entry['defaults'] : [];
            $requirements = isset($entry['requirements']) && is_array($entry['requirements']) ? $entry['requirements'] : [];

            $controllers[] = new ControllerRouteDefinition(
                $httpMethod,
                $path,
                $controller,
                $name === '' ? null : $name,
                $defaults,
                $requirements,
            );
        }

        return $controllers;
    }
}

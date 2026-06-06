<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Template;

use PhrameCMS\Core\Contracts\TemplateRendererInterface;
use RuntimeException;
use Throwable;

final class TemplateRendererFactory
{
    /**
     * @var list<string>
     */
    private const PREFERRED_RENDERER_CANDIDATES = [
        'PhrameCMS\\TwigBridge\\TwigBridge',
    ];

    public static function createDefault(): ?TemplateRendererInterface
    {
        $configured = trim((string) (getenv('PHRAME_TEMPLATE_RENDERER') ?: ''));

        if ($configured !== '') {
            return self::fromConfiguration($configured);
        }

        return self::createPreferredRenderer();
    }

    private static function fromConfiguration(string $configured): ?TemplateRendererInterface
    {
        $mode = TemplateRendererMode::fromConfiguration($configured);

        if ($mode === TemplateRendererMode::None) {
            return null;
        }

        if ($mode === TemplateRendererMode::Bridge) {
            return self::createPreferredRenderer();
        }

        if (!class_exists($configured)) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_TEMPLATE_RENDERER "%s": class was not found.',
                $configured,
            ));
        }

        try {
            $renderer = new $configured();
        } catch (Throwable $exception) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_TEMPLATE_RENDERER "%s": class could not be instantiated (%s).',
                $configured,
                $exception->getMessage(),
            ), (int) $exception->getCode(), $exception);
        }

        if (!$renderer instanceof TemplateRendererInterface) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_TEMPLATE_RENDERER "%s": class must implement %s.',
                $configured,
                TemplateRendererInterface::class,
            ));
        }

        return $renderer;
    }

    private static function createPreferredRenderer(): ?TemplateRendererInterface
    {
        foreach (self::PREFERRED_RENDERER_CANDIDATES as $rendererClass) {
            if (!class_exists($rendererClass)) {
                continue;
            }

            if (!$rendererClass::isAvailable()) {
                continue;
            }

            try {
                return new $rendererClass();
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }
}
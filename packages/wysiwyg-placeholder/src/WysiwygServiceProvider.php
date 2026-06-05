<?php

declare(strict_types=1);

namespace Lume\Plugin\WysiwygPlaceholder;

use Lume\Core\Contracts\ContainerBuilderInterface;
use Lume\Core\Contracts\ServiceProviderInterface;

final class WysiwygServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilderInterface $container): void
    {
        $container->set(WysiwygRouteProvider::class, static fn (): WysiwygRouteProvider => new WysiwygRouteProvider());
        $container->tag('route.provider', WysiwygRouteProvider::class);
    }

    public function boot(ContainerBuilderInterface $container): void
    {
        // No boot logic required for this placeholder plugin.
    }
}

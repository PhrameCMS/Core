<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Contracts;

interface ServiceProviderInterface
{
    public function register(ContainerBuilderInterface $container): void;

    public function boot(ContainerBuilderInterface $container): void;
}

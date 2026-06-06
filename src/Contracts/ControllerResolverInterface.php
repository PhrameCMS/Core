<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Contracts;

interface ControllerResolverInterface
{
    /**
     * @return callable(\PhrameCMS\Core\Http\Request, \PhrameCMS\Core\Contracts\ContainerBuilderInterface): \PhrameCMS\Core\Http\Response
     */
    public function resolve(string $controllerReference, ContainerBuilderInterface $container): callable;
}
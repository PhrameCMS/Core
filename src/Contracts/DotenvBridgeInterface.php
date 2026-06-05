<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Contracts;

interface DotenvBridgeInterface
{
    public function loadEnv(string $envPath): void;
}
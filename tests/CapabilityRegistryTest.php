<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\CapabilityRegistry;

final class CapabilityRegistryTest extends TestCase
{
    public function testAddAndAllNormalizeDeduplicateAndSort(): void
    {
        $registry = new CapabilityRegistry();

        $registry->add('  feature.zeta  ');
        $registry->add('feature.alpha');
        $registry->add('feature.alpha');
        $registry->add('   ');
        $registry->addMany(['feature.beta', ' feature.alpha ', '']);

        self::assertSame(['feature.alpha', 'feature.beta', 'feature.zeta'], $registry->all());
    }
}

<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Unit;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle as NativeDoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle as NativeMigrationsBundle;
use PHPUnit\Framework\TestCase;
use SymPress\DoctrineBundle\DoctrineBundle;
use SymPress\DoctrineBundle\DoctrineMigrationsBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class BundleContractTest extends TestCase
{
    public function testAdaptersKeepNativeExtensionsAndAllCompilerPasses(): void
    {
        foreach (
            [
            [new DoctrineBundle(), new NativeDoctrineBundle(), 'doctrine'],
            [new DoctrineMigrationsBundle(), new NativeMigrationsBundle(), 'doctrine_migrations'],
            ] as [$adapter, $native, $alias]
        ) {
            $nativeExtension = $native->getContainerExtension();
            $extension = $adapter->getContainerExtension();
            self::assertNotNull($nativeExtension);
            self::assertNotNull($extension);
            self::assertSame($nativeExtension::class, $extension::class);
            self::assertSame($alias, $extension->getAlias());
            self::assertSame($adapter->getContainerExtension(), $adapter->getContainerExtension());
            $actual = new ContainerBuilder();
            $expected = new ContainerBuilder();
            $adapter->build($actual);
            $native->build($expected);
            self::assertSame(
                array_map(static fn (object $pass): string => $pass::class, $expected->getCompilerPassConfig()->getPasses()),
                array_map(static fn (object $pass): string => $pass::class, $actual->getCompilerPassConfig()->getPasses()),
            );
            self::assertSame($native->getPath(), $adapter->getPath());
            self::assertSame($native->getNamespace(), $adapter->getNamespace());
            self::assertSame(dirname(__DIR__, 2), $adapter->path());
        }
    }
}

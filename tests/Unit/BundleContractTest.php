<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Unit;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle as NativeDoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle as NativeMigrationsBundle;
use PHPUnit\Framework\TestCase;
use SymPress\DoctrineBundle\DoctrineBundle;
use SymPress\DoctrineBundle\DoctrineMigrationsBundle;
use SymPress\DoctrineBundle\Optional\MakerBundle;
use SymPress\DoctrineBundle\Optional\SecurityBundle;
use Symfony\Bundle\MakerBundle\MakerBundle as NativeMakerBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle as NativeSecurityBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class BundleContractTest extends TestCase
{
    public function testAdaptersKeepNativeExtensionsAndAllCompilerPasses(): void
    {
        foreach (
            [
            [new DoctrineBundle(), new NativeDoctrineBundle(), 'doctrine'],
            [new DoctrineMigrationsBundle(), new NativeMigrationsBundle(), 'doctrine_migrations'],
            [new SecurityBundle(), new NativeSecurityBundle(), 'security'],
            [new MakerBundle(), new NativeMakerBundle(), 'maker'],
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
            $actual->registerExtension(clone $extension);
            $expected->registerExtension(clone $nativeExtension);
            $adapter->build($actual);
            $native->build($expected);
            self::assertSame(
                array_map(static fn (object $pass): string => $pass::class, $expected->getCompilerPassConfig()->getPasses()),
                array_map(static fn (object $pass): string => $pass::class, $actual->getCompilerPassConfig()->getPasses()),
            );
            self::assertSame($native->getPath(), $adapter->getPath());
            self::assertSame($native->getNamespace(), $adapter->getNamespace());
            self::assertSame($native->getName(), $adapter->getName());
            self::assertSame(dirname(__DIR__, 2), $adapter->path());
        }
    }
}

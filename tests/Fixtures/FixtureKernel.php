<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures;

use SymPress\Kernel\EnvConfig;
use SymPress\Kernel\Container;
use SymPress\Kernel\Bundle\BundleRegistry;
use SymPress\Kernel\Kernel\AbstractKernel;
use SymPress\Kernel\WpContext;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class FixtureKernel extends AbstractKernel
{
    public int $configureCalls = 0;

    public function __construct(private readonly string $cacheRoot, private readonly ?string $databaseUrl = null)
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(__DIR__ . '/Project', $this->cacheRoot . '/project');
        parent::__construct($this->cacheRoot . '/project', 'test', false, new EnvConfig(), WpContext::new()->force(WpContext::CORE));
        $filesystem->mkdir($this->getCacheDir());
        $_ENV['DOCTRINE_FIXTURE_URL'] = $this->databaseUrl ?? 'sqlite:///' . $this->getCacheDir() . '/primary.sqlite';
    }

    public function getCacheDir(): string
    {
        return $this->cacheRoot . '/cache';
    }

    /** @return array<int, string> */
    public function configureContainer(ContainerBuilder $builder, Container $container, BundleRegistry $bundles): array
    {
        ++$this->configureCalls;
        return parent::configureContainer($builder, $container, $bundles);
    }

    public function getBuildDir(): string
    {
        return $this->cacheRoot . '/build';
    }

    public function getLogDir(): string
    {
        return $this->cacheRoot . '/log';
    }

}

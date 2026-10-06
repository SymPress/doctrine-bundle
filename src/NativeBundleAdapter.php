<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle;

use SymPress\Kernel\Bundle\BundleInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/** Bridges kernel discovery while preserving the native bundle lifecycle. */
abstract class NativeBundleAdapter extends Bundle implements BundleInterface
{
    use KernelBundleTrait;

    protected function __construct(private readonly Bundle $native)
    {
    }

    public function getPath(): string
    {
        return $this->native->getPath();
    }

    public function getNamespace(): string
    {
        return $this->native->getNamespace();
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return $this->native->getContainerExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $this->native->build($container);
    }

    public function setContainer(?ContainerInterface $container): void
    {
        parent::setContainer($container);
        $this->native->setContainer($container);
    }

    public function boot(): void
    {
        $this->native->boot();
    }

    public function shutdown(): void
    {
        $this->native->shutdown();
    }
}

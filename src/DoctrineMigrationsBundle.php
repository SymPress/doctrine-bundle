<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle;

use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle as UpstreamMigrationsBundle;
use SymPress\Framework\SymPressFrameworkBundle;
use Symfony\Component\DependencyInjection\Kernel\RequiredBundle;

#[RequiredBundle(SymPressFrameworkBundle::class)]
final class DoctrineMigrationsBundle extends NativeBundleAdapter
{
    public function __construct()
    {
        parent::__construct(new UpstreamMigrationsBundle());
    }
}

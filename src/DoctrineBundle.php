<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle as UpstreamDoctrineBundle;
use SymPress\Framework\SymPressFrameworkBundle;
use Symfony\Component\DependencyInjection\Kernel\RequiredBundle;

#[RequiredBundle(SymPressFrameworkBundle::class)]
#[RequiredBundle(DoctrineMigrationsBundle::class)]
final class DoctrineBundle extends NativeBundleAdapter
{
    public function __construct()
    {
        parent::__construct(new UpstreamDoctrineBundle());
    }
}

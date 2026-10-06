<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Optional;

use SymPress\DoctrineBundle\DoctrineBundle;
use SymPress\DoctrineBundle\NativeBundleAdapter;
use Symfony\Bundle\MakerBundle\MakerBundle as NativeMakerBundle;
use Symfony\Component\DependencyInjection\Kernel\RequiredBundle;

#[RequiredBundle(DoctrineBundle::class)]
final class MakerBundle extends NativeBundleAdapter
{
    public function __construct()
    {
        parent::__construct(new NativeMakerBundle());
    }
}

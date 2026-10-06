<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Optional;

use SymPress\DoctrineBundle\NativeBundleAdapter;
use SymPress\Framework\SymPressFrameworkBundle;
use Symfony\Bridge\Doctrine\DependencyInjection\Security\UserProvider\EntityFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Bundle\SecurityBundle\SecurityBundle as NativeSecurityBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Kernel\RequiredBundle;

#[RequiredBundle(SymPressFrameworkBundle::class)]
final class SecurityBundle extends NativeBundleAdapter
{
    public function __construct()
    {
        parent::__construct(new NativeSecurityBundle());
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        // SymPress registers extensions and builds bundles in one ordered loop.
        // If Doctrine was built first, its native security factory hook was skipped.
        if (!$container->hasExtension('doctrine')) {
            return;
        }

        $security = $container->getExtension('security');
        if (!($security instanceof SecurityExtension)) {
            return;
        }

        $security->addUserProviderFactory(new EntityFactory('entity', 'doctrine.orm.security.user.provider'));
    }
}

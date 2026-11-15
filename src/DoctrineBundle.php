<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle as UpstreamDoctrineBundle;
use SymPress\DoctrineBundle\Dbal\StrictMysqliReportingMiddleware;
use SymPress\Framework\SymPressFrameworkBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Kernel\RequiredBundle;

#[RequiredBundle(SymPressFrameworkBundle::class)]
#[RequiredBundle(DoctrineMigrationsBundle::class)]
final class DoctrineBundle extends NativeBundleAdapter
{
    public function __construct()
    {
        parent::__construct(new UpstreamDoctrineBundle());
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        // Highest priority makes it the innermost middleware, so it sees and wraps the raw mysqli driver.
        $container->register('sympress.doctrine.dbal.strict_mysqli_reporting', StrictMysqliReportingMiddleware::class)
            ->addTag('doctrine.middleware', ['priority' => 1024]);
    }
}

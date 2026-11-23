<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle as UpstreamDoctrineBundle;
use Doctrine\ORM\Tools\ToolEvents;
use SymPress\DoctrineBundle\Dbal\StrictMysqliReportingMiddleware;
use SymPress\DoctrineBundle\Dbal\WordPressTablesSchemaFilter;
use SymPress\DoctrineBundle\DependencyInjection\WordPressSchemaFilterPass;
use SymPress\DoctrineBundle\Orm\WordPressTablesSchemaListener;
use SymPress\Framework\SymPressFrameworkBundle;
use SymPress\Kernel\SiteConfig;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Kernel\RequiredBundle;
use Symfony\Component\DependencyInjection\Reference;

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
        // Runs before Doctrine's DbalSchemaFilterPass collects the doctrine.dbal.schema_filter tags.
        $container->register(WordPressSchemaFilterPass::FILTER_ID, WordPressTablesSchemaFilter::class)
            ->setArguments([new Reference(SiteConfig::class)]);
        $container->addCompilerPass(new WordPressSchemaFilterPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1024);
        // Without a connection attribute, Doctrine attaches the listener to every connection.
        $container->register('sympress.doctrine.orm.wordpress_schema_listener', WordPressTablesSchemaListener::class)
            ->setArguments([new Reference(WordPressSchemaFilterPass::FILTER_ID)])
            ->addTag('doctrine.event_listener', ['event' => ToolEvents::postGenerateSchema]);
    }
}

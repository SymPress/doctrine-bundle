<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Applies the WordPress tables filter to every DBAL connection, not only the default one. Doctrine ANDs all
 * tagged filters per connection, so a configured schema_filter keeps working alongside it.
 */
final class WordPressSchemaFilterPass implements CompilerPassInterface
{
    public const string FILTER_ID = 'sympress.doctrine.dbal.wordpress_schema_filter';

    public function process(ContainerBuilder $container): void
    {
        $connections = $container->hasParameter('doctrine.connections')
            ? $container->getParameter('doctrine.connections')
            : [];
        if (!is_array($connections) || !$container->hasDefinition(self::FILTER_ID)) {
            return;
        }

        $filter = $container->getDefinition(self::FILTER_ID);
        foreach (array_keys($connections) as $connection) {
            $filter->addTag('doctrine.dbal.schema_filter', ['connection' => (string) $connection]);
        }
    }
}

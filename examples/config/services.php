<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('doctrine', [
        'dbal' => [
            'url'           => '%env(DATABASE_URL)%',
            'schema_filter' => '~^(enterprise_|doctrine_migration_versions$)~',
        ],
        'orm'  => [
            'mappings' => [
        'Application' => [
                'type' => 'attribute',
        'is_bundle'    => false,
                'dir'  => '%kernel.project_dir%/app/Entity',
        'prefix'       => 'App\\Entity',
            ],
            ],
        ],
    ]);
    $container->extension('doctrine_migrations', [
        'migrations_paths' => ['App\\Migrations' => '%kernel.project_dir%/migrations'],
        'enable_profiler'  => false,
    ]);
    $container->services()->defaults()->autowire()->autoconfigure()->load('App\\', '../app/');
};

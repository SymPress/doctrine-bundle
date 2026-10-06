<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use SymPress\DoctrineBundle\Tests\Fixtures\InvoiceRepository;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;
use SymPress\DoctrineBundle\Tests\Fixtures\Migrations\Version202610060001;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        'secret' => 'doctrine-fixture-secret-for-disposable-tests-only',
        'form' => ['enabled' => true],
        'validation' => ['enabled' => true, 'enable_attributes' => true],
        'serializer' => ['enabled' => true],
        'property_info' => ['enabled' => true],
        'property_access' => ['enabled' => true],
        'router' => ['resource' => '%kernel.project_dir%/config/routes.php'],
        'messenger' => ['buses' => ['messenger.bus.default' => ['middleware' => ['doctrine_transaction']]]],
    ]);
    $container->extension('doctrine', [
        'dbal' => [
            'types' => ['normalized_email' => SymPress\DoctrineBundle\Tests\Fixtures\NormalizedEmailType::class],
            'connections' => [
                'default' => ['url' => '%env(DOCTRINE_FIXTURE_URL)%', 'schema_filter' => '~^(enterprise_|doctrine_migration_versions$)~'],
                'reporting' => ['driver' => 'pdo_sqlite', 'path' => '%kernel.cache_dir%/reporting.sqlite'],
            ],
        ],
        'orm' => [
            'entity_managers' => [
                'default' => [
                    'connection' => 'default',
                    'dql' => ['string_functions' => ['TEXT_LENGTH' => Doctrine\ORM\Query\AST\Functions\LengthFunction::class]],
                    'filters' => ['invoice_reference' => ['class' => SymPress\DoctrineBundle\Tests\Fixtures\InvoiceFilter::class]],
                    'mappings' => ['Fixture' => [
                        'type' => 'attribute', 'is_bundle' => false,
                        'dir' => dirname((string) (new ReflectionClass(Invoice::class))->getFileName()),
                        'prefix' => 'SymPress\\DoctrineBundle\\Tests\\Fixtures\\Entity',
                    ]],
                ],
                'reporting' => [
                    'connection' => 'reporting',
                    'mappings' => ['Fixture' => [
                        'type' => 'attribute', 'is_bundle' => false,
                        'dir' => dirname((string) (new ReflectionClass(Invoice::class))->getFileName()),
                        'prefix' => 'SymPress\\DoctrineBundle\\Tests\\Fixtures\\Entity',
                    ]],
                ],
            ],
        ],
    ]);
    $container->extension('doctrine_migrations', [
        'migrations_paths' => [
            'App\\Migrations' => '%kernel.project_dir%/migrations',
            'SymPress\\DoctrineBundle\\Tests\\Fixtures\\Migrations' => dirname((string) (new ReflectionClass(Version202610060001::class))->getFileName()),
        ],
        'enable_profiler' => false,
    ]);
    $services = $container->services()->defaults()->autowire()->autoconfigure();
    $services->set(InvoiceRepository::class)->public();
    $services->set(SymPress\DoctrineBundle\Tests\Fixtures\InvoiceListener::class)->public();
    $services->set(SymPress\DoctrineBundle\Tests\Fixtures\CreateInvoiceHandler::class);
    $services->set(SymPress\DoctrineBundle\Tests\Fixtures\QueryLogger::class)->public();
    $services->set('test.query_logging', Doctrine\DBAL\Logging\Middleware::class)
        ->args([service(SymPress\DoctrineBundle\Tests\Fixtures\QueryLogger::class)])
        ->tag('doctrine.middleware', ['connection' => 'default']);
    $services->alias(EntityManagerInterface::class, 'doctrine.orm.entity_manager')->public();
    $services->alias(ManagerRegistry::class, 'doctrine')->public();
    $services->alias('test.form_factory', 'form.factory')->public();
    $services->alias('test.validator', 'validator')->public();
    $services->alias('test.migrations', 'doctrine.migrations.dependency_factory')->public();
    $services->alias('test.message_bus', 'messenger.bus.default')->public();
    $services->alias('test.user_provider', 'security.user.provider.concrete.enterprise')->public();
    $services->alias('test.entity_resolver', 'doctrine.orm.entity_value_resolver')->public();
    $container->extension('security', [
        'providers' => ['enterprise' => ['entity' => ['class' => SymPress\DoctrineBundle\Tests\Fixtures\Entity\StaffMember::class, 'property' => 'email']]],
        'firewalls' => ['enterprise' => ['pattern' => '^/enterprise', 'stateless' => true, 'provider' => 'enterprise']],
    ]);
};

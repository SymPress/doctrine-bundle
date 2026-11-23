<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use SymPress\DoctrineBundle\Tests\Fixtures\Migrations\Version202610060001;
use SymPress\DoctrineBundle\Tests\Support\KernelTestCase;
use SymPress\Kernel\Console\ConsoleApplicationFactory;
use SymPress\Kernel\Console\LintContainerCommand;
use Symfony\Component\Console\Tester\CommandTester;

final class MigrationsAndConsoleTest extends KernelTestCase
{
    public function testMigrationUpDownAndMetadataThroughNativeConsole(): void
    {
        foreach (['--up', '--down'] as $direction) {
            $tester = new CommandTester($this->console()->find('doctrine:migrations:execute'));
            $arguments = ['versions' => [Version202610060001::class], $direction => true, '--no-interaction' => true];
            self::assertSame(0, $tester->execute($arguments), $tester->getDisplay());
            $connection = $this->manager()->getConnection();
            $count = (int) $connection->fetchOne('SELECT COUNT(*) FROM doctrine_migration_versions');
            self::assertSame($direction === '--up' ? 1 : 0, $count);
            if ($direction !== '--up') {
                continue;
            }

            self::assertSame(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM enterprise_audit'));
            $this->restartKernel();
        }
    }

    public function testSchemaDiffNeverProposesDroppingForeignWordPressTables(): void
    {
        $manager = $this->manager();
        $connection = $manager->getConnection();
        $connection->executeStatement('CREATE TABLE wp_posts (id INT NOT NULL PRIMARY KEY, title VARCHAR(100))');
        $sql = (new SchemaTool($manager))->getUpdateSchemaSql($manager->getMetadataFactory()->getAllMetadata());
        self::assertNotEmpty($sql);
        self::assertStringNotContainsString('wp_posts', implode("\n", $sql));
        $connection->executeStatement("INSERT INTO wp_posts VALUES (1, 'Preserve this content')");
        self::assertSame('Preserve this content', $connection->fetchOne('SELECT title FROM wp_posts WHERE id = 1'));
    }

    public function testSchemaToolsLeaveMappedWordPressTablesAlone(): void
    {
        $manager = $this->manager();
        $connection = $manager->getConnection();
        $connection->executeStatement(
            'CREATE TABLE wp_users (ID INTEGER NOT NULL PRIMARY KEY, user_login VARCHAR(60) NOT NULL, '
            . 'user_pass VARCHAR(255) NOT NULL)',
        );
        $connection->executeStatement("INSERT INTO wp_users VALUES (1, 'admin', 'password-hash')");
        $metadata = $manager->getMetadataFactory()->getAllMetadata();
        $createSql = (new SchemaTool($manager))->getCreateSchemaSql($metadata);
        self::assertStringNotContainsString('wp_users', implode("\n", $createSql));

        $tester = new CommandTester($this->console()->find('doctrine:schema:update'));
        self::assertSame(0, $tester->execute(['--force' => true]), $tester->getDisplay());

        self::assertSame('password-hash', $connection->fetchOne('SELECT user_pass FROM wp_users WHERE ID = 1'));
        self::assertSame([], (new SchemaTool($manager))->getUpdateSchemaSql($metadata));
    }

    public function testSchemaDiffKeepsWordPressTablesWithoutAConfiguredFilter(): void
    {
        // The reporting connection configures no schema_filter, as a default installation would.
        $registry = $this->kernel->getContainer()->get(ManagerRegistry::class);
        self::assertInstanceOf(ManagerRegistry::class, $registry);
        $manager = $registry->getManager('reporting');
        self::assertInstanceOf(EntityManagerInterface::class, $manager);
        $connection = $manager->getConnection();
        foreach (['wp_posts', 'wp_2_posts', 'unrelated_table'] as $table) {
            $connection->executeStatement(sprintf('CREATE TABLE %s (id INT NOT NULL PRIMARY KEY)', $table));
        }

        $metadata = $manager->getMetadataFactory()->getAllMetadata();
        $sql = implode("\n", (new SchemaTool($manager))->getUpdateSchemaSql($metadata));

        self::assertStringContainsString('CREATE TABLE enterprise_invoice', $sql);
        self::assertStringNotContainsString('wp_', $sql);
        self::assertStringContainsString('DROP TABLE unrelated_table', $sql);
    }

    public function testWordPressFilterComposesWithTheConfiguredFilterForMigrations(): void
    {
        // Migrations' diff reads the same connection-level filter as the ORM schema tool.
        $filter = $this->manager()->getConnection()->getConfiguration()->getSchemaAssetsFilter();
        self::assertTrue($filter('enterprise_invoice'));
        self::assertTrue($filter('doctrine_migration_versions'));
        self::assertFalse($filter('wp_posts'));
        self::assertFalse($filter('unrelated_table'));
    }

    public function testNativeConsoleCommandsAreLoadedAndExecute(): void
    {
        $factory = $this->kernel->getContainer()->get(ConsoleApplicationFactory::class);
        self::assertInstanceOf(ConsoleApplicationFactory::class, $factory);
        $console = $factory->create();
        $commands = [
            'doctrine:mapping:info', 'doctrine:schema:validate', 'doctrine:migrations:migrate',
            'doctrine:migrations:diff', 'doctrine:migrations:execute',
        ];
        foreach ($commands as $command) {
            self::assertTrue($console->has($command), $command);
        }
        $tester = new CommandTester($console->find('doctrine:mapping:info'));
        self::assertSame(0, $tester->execute([]));
        self::assertStringContainsString('Invoice', $tester->getDisplay());
        $tester = new CommandTester($console->find('doctrine:schema:validate'));
        self::assertSame(0, $tester->execute(['--skip-sync' => true]), $tester->getDisplay());
        $tester = new CommandTester($console->find('doctrine:migrations:migrate'));
        self::assertSame(0, $tester->execute(['--no-interaction' => true]));
        self::assertSame(0, (int) $this->manager()->getConnection()->fetchOne('SELECT COUNT(*) FROM enterprise_audit'));
        $tester = new CommandTester(new LintContainerCommand($this->kernel->getContainer()));
        self::assertSame(0, $tester->execute([]), $tester->getDisplay());
    }
}

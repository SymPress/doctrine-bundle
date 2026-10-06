<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Integration;

use Doctrine\ORM\Tools\SchemaTool;
use SymPress\DoctrineBundle\Tests\Fixtures\Migrations\Version202610060001;
use SymPress\DoctrineBundle\Tests\Support\KernelTestCase;
use SymPress\Kernel\Console\ConsoleApplicationFactory;
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
        $tester = new CommandTester($console->find('doctrine:migrations:migrate'));
        self::assertSame(0, $tester->execute(['--no-interaction' => true]));
        self::assertSame(0, (int) $this->manager()->getConnection()->fetchOne('SELECT COUNT(*) FROM enterprise_audit'));
    }
}

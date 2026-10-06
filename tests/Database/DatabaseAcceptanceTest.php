<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Database;

use Doctrine\DBAL\Schema\Name\OptionallyQualifiedName;
use Doctrine\ORM\Tools\SchemaTool;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;
use SymPress\DoctrineBundle\Tests\Fixtures\FixtureKernel;
use SymPress\DoctrineBundle\Tests\Fixtures\Migrations\Version202610060001;
use SymPress\DoctrineBundle\Tests\Support\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class DatabaseAcceptanceTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $url = getenv('DOCTRINE_TEST_DATABASE_URL');
        if ($url === false || $url === '') {
            self::markTestSkipped('Set DOCTRINE_TEST_DATABASE_URL for the disposable real database acceptance suite.');
        }
        if (parse_url($url, PHP_URL_PATH) !== '/sympress_doctrine_test') {
            throw new \RuntimeException('Database acceptance requires the disposable sympress_doctrine_test database.');
        }
        parent::setUp();
        $connection = $this->manager()->getConnection();
        $tables = [
            'enterprise_invoice_line', 'enterprise_invoice', 'enterprise_staff',
            'enterprise_audit', 'doctrine_migration_versions', 'wp_posts',
        ];
        foreach ($tables as $table) {
            $connection->executeStatement('DROP TABLE IF EXISTS ' . $connection->quoteSingleIdentifier($table));
        }
    }

    protected function createKernel(): FixtureKernel
    {
        $url = getenv('DOCTRINE_TEST_DATABASE_URL');
        return new FixtureKernel($this->cacheRoot, is_string($url) ? $url : null);
    }

    public function testRealDatabasePersistenceRollbackForeignTableScopeAndMigrations(): void
    {
        $this->createSchema();
        $manager = $this->manager();
        $connection = $manager->getConnection();
        $invoice = new Invoice('DATABASE-001');
        $invoice->addLine('Database integration');
        $manager->persist($invoice);
        $manager->flush();
        $id = $invoice->getId();
        $manager->clear();
        $loaded = $manager->find(Invoice::class, $id);
        self::assertInstanceOf(Invoice::class, $loaded);
        self::assertCount(1, $loaded->getLines());
        $connection->beginTransaction();
        $connection->executeStatement('UPDATE enterprise_invoice SET reference = ? WHERE id = ?', ['ROLLBACK', $id]);
        $connection->rollBack();
        self::assertSame('DATABASE-001', $connection->fetchOne(
            'SELECT reference FROM enterprise_invoice WHERE id = ?',
            [$id],
        ));
        $connection->executeStatement('CREATE TABLE wp_posts (id INT NOT NULL PRIMARY KEY, title VARCHAR(100))');
        $connection->executeStatement("INSERT INTO wp_posts VALUES (1, 'Foreign data')");
        $sql = (new SchemaTool($manager))->getUpdateSchemaSql($manager->getMetadataFactory()->getAllMetadata());
        self::assertStringNotContainsString('wp_posts', implode("\n", $sql));
        self::assertSame('Foreign data', $connection->fetchOne('SELECT title FROM wp_posts WHERE id = 1'));
        foreach (['--up', '--down'] as $direction) {
            $tester = new CommandTester($this->console()->find('doctrine:migrations:execute'));
            $arguments = ['versions' => [Version202610060001::class], $direction => true, '--no-interaction' => true];
            self::assertSame(0, $tester->execute($arguments), $tester->getDisplay());
            $count = (int) $this->manager()->getConnection()->fetchOne(
                'SELECT COUNT(*) FROM doctrine_migration_versions',
            );
            self::assertSame($direction === '--up' ? 1 : 0, $count);
            if ($direction !== '--up') {
                continue;
            }

            $this->restartKernel();
        }
        $tables = array_map(
            static fn (OptionallyQualifiedName $name): string => $name->toString(),
            $connection->createSchemaManager()->introspectTableNames(),
        );
        self::assertNotContains('enterprise_audit', $tables);
        self::assertSame('Foreign data', $connection->fetchOne('SELECT title FROM wp_posts WHERE id = 1'));
    }
}

<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Database;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use SymPress\DoctrineBundle\Tests\Fixtures\FixtureKernel;
use SymPress\DoctrineBundle\Tests\Support\KernelTestCase;

/** Runs with MYSQLI_REPORT_OFF, as WordPress' wpdb sets it, against a disposable mysqli database. */
final class MysqliErrorReportingTest extends KernelTestCase
{
    private int $initialReportMode;

    protected function setUp(): void
    {
        $url = getenv('DOCTRINE_TEST_DATABASE_URL');
        if ($url === false || !str_starts_with($url, 'mysqli://')) {
            self::markTestSkipped('Set DOCTRINE_TEST_DATABASE_URL to a disposable mysqli:// database.');
        }
        if (parse_url($url, PHP_URL_PATH) !== '/sympress_doctrine_test') {
            throw new \RuntimeException('Database acceptance requires the disposable sympress_doctrine_test database.');
        }

        $this->initialReportMode = (new \mysqli_driver())->report_mode;
        mysqli_report(MYSQLI_REPORT_OFF);
        parent::setUp();
        $this->manager()->getConnection()->executeStatement('DROP TABLE IF EXISTS sympress_mysqli_probe');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (!isset($this->initialReportMode)) {
            return;
        }

        mysqli_report($this->initialReportMode);
    }

    protected function createKernel(): FixtureKernel
    {
        $url = getenv('DOCTRINE_TEST_DATABASE_URL');

        return new FixtureKernel($this->cacheRoot, is_string($url) ? $url : null);
    }

    public function testMissingTableRaisesTableNotFound(): void
    {
        $this->expectException(TableNotFoundException::class);
        $this->manager()->getConnection()->fetchOne('SELECT 1 FROM sympress_mysqli_missing');
    }

    public function testFailedWriteIsNotSilentAndWordPressModeIsRestored(): void
    {
        $connection = $this->manager()->getConnection();
        $connection->executeStatement('CREATE TABLE sympress_mysqli_probe (id INT NOT NULL PRIMARY KEY)');
        $connection->insert('sympress_mysqli_probe', ['id' => 1]);
        self::assertSame(MYSQLI_REPORT_OFF, (new \mysqli_driver())->report_mode);

        try {
            $connection->insert('sympress_mysqli_probe', ['id' => 1]);
            self::fail('A duplicate key must raise an exception.');
        } catch (UniqueConstraintViolationException) {
            self::assertSame(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM sympress_mysqli_probe'));
        }

        self::assertSame(MYSQLI_REPORT_OFF, (new \mysqli_driver())->report_mode);
    }
}

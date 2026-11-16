<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Unit;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver as SqliteDriver;
use PHPUnit\Framework\TestCase;
use SymPress\DoctrineBundle\Dbal\StrictMysqliReporting;
use SymPress\DoctrineBundle\Dbal\StrictMysqliReportingConnection;
use SymPress\DoctrineBundle\Dbal\StrictMysqliReportingMiddleware;

final class StrictMysqliReportingTest extends TestCase
{
    public function testNonMysqliConnectionsAreNotWrapped(): void
    {
        $connection = (new StrictMysqliReportingMiddleware())->wrap(new SqliteDriver())->connect(['memory' => true]);

        self::assertNotInstanceOf(StrictMysqliReportingConnection::class, $connection);
    }

    public function testConnectFailureTraceDoesNotExposeThePassword(): void
    {
        if (!extension_loaded('mysqli')) {
            self::markTestSkipped('Requires ext-mysqli.');
        }

        $driver = new class extends AbstractMySQLDriver {
            public function connect(#[\SensitiveParameter] array $params): Connection
            {
                throw new \RuntimeException(sprintf('Connection to %s refused.', $params['host'] ?? 'localhost'));
            }
        };

        try {
            (new StrictMysqliReportingMiddleware())
                ->wrap($driver)
                ->connect(['driver' => 'mysqli', 'password' => 'db-secret']);
            self::fail('The connection must fail.');
        } catch (\RuntimeException $exception) {
            self::assertFalse($this->containsValue($exception->getTrace(), 'db-secret'));
        }
    }

    public function testStrictModeAppliesOnlyDuringTheCallAndIsRestoredAfterFailures(): void
    {
        if (!extension_loaded('mysqli')) {
            self::markTestSkipped('Requires ext-mysqli.');
        }

        $initial = (new \mysqli_driver())->report_mode;
        mysqli_report(MYSQLI_REPORT_OFF);

        try {
            $during = StrictMysqliReporting::call(static fn (): int => (new \mysqli_driver())->report_mode);
            self::assertSame(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT, $during);

            try {
                StrictMysqliReporting::call(static fn () => throw new \RuntimeException('failed'));
            } catch (\RuntimeException) {
                // Expected; the mode must be restored anyway.
            }

            self::assertSame(MYSQLI_REPORT_OFF, (new \mysqli_driver())->report_mode);
        } finally {
            mysqli_report($initial);
        }
    }

    private function containsValue(mixed $haystack, string $needle): bool
    {
        if ($haystack instanceof \Closure) {
            $haystack = (new \ReflectionFunction($haystack))->getStaticVariables();
        }

        if (!is_array($haystack)) {
            return $haystack === $needle;
        }

        foreach ($haystack as $value) {
            if ($this->containsValue($value, $needle)) {
                return true;
            }
        }

        return false;
    }
}

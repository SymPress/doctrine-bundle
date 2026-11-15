<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Dbal;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;

final class StrictMysqliReportingConnection extends AbstractConnectionMiddleware
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct($connection);
    }

    public function prepare(string $sql): Statement
    {
        return new StrictMysqliReportingStatement(StrictMysqliReporting::call(fn () => $this->connection->prepare($sql)));
    }

    public function query(string $sql): Result
    {
        return new StrictMysqliReportingResult(StrictMysqliReporting::call(fn () => $this->connection->query($sql)));
    }

    public function quote(string $value): string
    {
        return StrictMysqliReporting::call(fn () => $this->connection->quote($value));
    }

    public function exec(string $sql): int|string
    {
        return StrictMysqliReporting::call(fn () => $this->connection->exec($sql));
    }

    public function lastInsertId(): int|string
    {
        return StrictMysqliReporting::call(fn () => $this->connection->lastInsertId());
    }

    public function beginTransaction(): void
    {
        StrictMysqliReporting::call(fn () => $this->connection->beginTransaction());
    }

    public function commit(): void
    {
        StrictMysqliReporting::call(fn () => $this->connection->commit());
    }

    public function rollBack(): void
    {
        StrictMysqliReporting::call(fn () => $this->connection->rollBack());
    }

    public function getServerVersion(): string
    {
        return StrictMysqliReporting::call(fn () => $this->connection->getServerVersion());
    }
}

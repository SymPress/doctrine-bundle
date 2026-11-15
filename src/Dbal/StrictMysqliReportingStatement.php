<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Dbal;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;

final class StrictMysqliReportingStatement extends AbstractStatementMiddleware
{
    public function __construct(private readonly Statement $statement)
    {
        parent::__construct($statement);
    }

    public function bindValue(int|string $param, mixed $value, ParameterType $type): void
    {
        StrictMysqliReporting::call(fn () => $this->statement->bindValue($param, $value, $type));
    }

    public function execute(): Result
    {
        return new StrictMysqliReportingResult(StrictMysqliReporting::call(fn () => $this->statement->execute()));
    }
}

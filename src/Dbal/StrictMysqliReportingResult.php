<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Dbal;

use Doctrine\DBAL\Driver\Middleware\AbstractResultMiddleware;
use Doctrine\DBAL\Driver\Result;

/** Fetches can fail too (lost connection); without strict mode mysqli reports that as the end of the data. */
final class StrictMysqliReportingResult extends AbstractResultMiddleware
{
    public function __construct(private readonly Result $result)
    {
        parent::__construct($result);
    }

    /** @return list<mixed>|false */
    public function fetchNumeric(): array|false
    {
        return StrictMysqliReporting::call(fn () => $this->result->fetchNumeric());
    }

    /** @return array<string, mixed>|false */
    public function fetchAssociative(): array|false
    {
        return StrictMysqliReporting::call(fn () => $this->result->fetchAssociative());
    }

    public function fetchOne(): mixed
    {
        return StrictMysqliReporting::call(fn () => $this->result->fetchOne());
    }

    /** @return list<list<mixed>> */
    public function fetchAllNumeric(): array
    {
        return StrictMysqliReporting::call(fn () => $this->result->fetchAllNumeric());
    }

    /** @return list<array<string, mixed>> */
    public function fetchAllAssociative(): array
    {
        return StrictMysqliReporting::call(fn () => $this->result->fetchAllAssociative());
    }

    /** @return list<mixed> */
    public function fetchFirstColumn(): array
    {
        return StrictMysqliReporting::call(fn () => $this->result->fetchFirstColumn());
    }

    public function free(): void
    {
        StrictMysqliReporting::call(fn () => $this->result->free());
    }
}

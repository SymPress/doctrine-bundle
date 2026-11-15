<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Dbal;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

/** Registered as the innermost DBAL middleware; leaves every non-mysqli connection untouched. */
final class StrictMysqliReportingMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new StrictMysqliReportingDriver($driver);
    }
}

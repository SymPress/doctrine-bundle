<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Dbal;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Driver\Mysqli\Driver as MysqliDriver;
use Doctrine\DBAL\DriverManager;

/** @phpstan-import-type Params from DriverManager */
final class StrictMysqliReportingDriver extends AbstractDriverMiddleware
{
    public function __construct(private readonly Driver $driver)
    {
        parent::__construct($driver);
    }

    /** @phpstan-param Params $params */
    public function connect(#[\SensitiveParameter] array $params): Connection
    {
        $mysqli = $this->driver instanceof MysqliDriver
            || ($params['driver'] ?? null) === 'mysqli'
            || is_a($params['driverClass'] ?? '', MysqliDriver::class, true);
        if (!$mysqli) {
            return $this->driver->connect($params);
        }

        return new StrictMysqliReportingConnection(StrictMysqliReporting::call(fn () => $this->driver->connect($params)));
    }
}

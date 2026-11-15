<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Dbal;

/**
 * WordPress' wpdb calls mysqli_report(MYSQLI_REPORT_OFF) for the whole process, while DBAL's mysqli
 * driver relies on mysqli exceptions. Doctrine calls run in strict mode; wpdb gets its mode back after each.
 */
final class StrictMysqliReporting
{
    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public static function call(callable $operation): mixed
    {
        $previous = (new \mysqli_driver())->report_mode;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            return $operation();
        } finally {
            mysqli_report($previous);
        }
    }
}

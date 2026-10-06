<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures;

use Psr\Log\AbstractLogger;

final class QueryLogger extends AbstractLogger
{
    /** @var list<string> */
    public array $queries = [];

    public function log(mixed $level, string|\Stringable $message, array $context = []): void
    {
        if (isset($context['sql']) && is_string($context['sql'])) {
            $this->queries[] = $context['sql'];
        }
    }
}

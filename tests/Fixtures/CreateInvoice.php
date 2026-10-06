<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures;

final readonly class CreateInvoice
{
    public function __construct(public string $reference, public bool $fail = false)
    {
    }
}

<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class NormalizedEmailType extends StringType
{
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new \InvalidArgumentException('An email must be a string.');
        }
        return strtolower($value);
    }
}

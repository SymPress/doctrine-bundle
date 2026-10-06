<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;

final class InvoiceFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if ($targetEntity->getName() !== Invoice::class) {
            return '';
        }
        return $targetTableAlias . '.reference = ' . $this->getParameter('reference');
    }
}

<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;

/** @extends ServiceEntityRepository<Invoice> */
final class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function findByReference(string $reference): ?Invoice
    {
        return $this->createQueryBuilder('invoice')
            ->where('invoice.reference = :reference')
            ->setParameter('reference', $reference)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;

#[AsMessageHandler]
final readonly class CreateInvoiceHandler
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    public function __invoke(CreateInvoice $message): void
    {
        $this->manager->persist(new Invoice($message->reference));
        $this->manager->flush();
        if ($message->fail) {
            throw new \RuntimeException('Messenger rollback probe.');
        }
    }
}

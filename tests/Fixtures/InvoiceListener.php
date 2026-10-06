<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;

#[AsDoctrineListener(event: Events::postPersist)]
final class InvoiceListener
{
    public int $persistedInvoices = 0;

    public function postPersist(PostPersistEventArgs $event): void
    {
        if ($event->getObject() instanceof Invoice) {
            ++$this->persistedInvoices;
        }
    }
}

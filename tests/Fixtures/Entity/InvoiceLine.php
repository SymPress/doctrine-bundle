<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'enterprise_invoice_line')]
class InvoiceLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'lines')]
        #[ORM\JoinColumn(nullable: false)]
        private Invoice $invoice,
        #[ORM\Column(length: 100)]
        private string $description,
    ) {
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}

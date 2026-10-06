<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SymPress\DoctrineBundle\Tests\Fixtures\InvoiceRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\Table(name: 'enterprise_invoice')]
#[UniqueEntity(fields: ['reference'])]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $reference;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    /** @var Collection<int, InvoiceLine> */
    #[ORM\OneToMany(targetEntity: InvoiceLine::class, mappedBy: 'invoice', cascade: ['persist'], orphanRemoval: true)]
    private Collection $lines;

    public function __construct(string $reference)
    {
        $this->reference = $reference;
        $this->lines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function rename(string $reference): void
    {
        $this->reference = $reference;
    }

    public function addLine(string $description): void
    {
        $this->lines->add(new InvoiceLine($this, $description));
    }

    /** @return Collection<int, InvoiceLine> */
    public function getLines(): Collection
    {
        return $this->lines;
    }
}

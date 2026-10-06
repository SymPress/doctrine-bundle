<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'enterprise_staff')]
class StaffMember implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: 'normalized_email', length: 180, unique: true)]
    private string $email;

    public function __construct(string $email)
    {
        if ($email === '') {
            throw new \InvalidArgumentException('An identifier is required.');
        }
        $this->email = $email;
        $this->id = Uuid::v7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        return ['ROLE_ENTERPRISE'];
    }

    /** @return non-empty-string */
    public function getUserIdentifier(): string
    {
        if ($this->email === '') {
            throw new \LogicException('Invalid persisted identifier.');
        }
        return $this->email;
    }
}

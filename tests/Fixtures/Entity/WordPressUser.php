<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

// Maps a WordPress-owned table read-only and partially, as applications commonly do.
#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'wp_users')]
class WordPressUser
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(name: 'ID')]
        private int $id,
        #[ORM\Column(name: 'user_login', length: 60)]
        private string $login,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }
}

<?php

declare(strict_types=1);

use SymPress\DoctrineBundle\DoctrineBundle;
use SymPress\DoctrineBundle\Optional\SecurityBundle;
use SymPress\DoctrineBundle\Optional\MakerBundle;

return [DoctrineBundle::class => ['all' => true], SecurityBundle::class => ['test' => true], MakerBundle::class => ['test' => true]];

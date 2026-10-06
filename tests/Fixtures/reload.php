<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;
use SymPress\DoctrineBundle\Tests\Fixtures\FixtureKernel;
use SymPress\Kernel\App;

require dirname(__DIR__) . '/bootstrap.php';

$root = getenv('DOCTRINE_RELOAD_ROOT');
if (!is_string($root) || $root === '') {
    throw new RuntimeException('Missing cache root for reload test.');
}
$kernel = new FixtureKernel($root);
App::new($kernel)->boot();
$manager = $kernel->getContainer()->get(EntityManagerInterface::class);
assert($manager instanceof EntityManagerInterface);
$invoice = $manager->getRepository(Invoice::class)->findOneBy(['reference' => 'CACHE-RELOAD']);
echo json_encode(['configured' => $kernel->configureCalls, 'reference' => $invoice?->getReference()], JSON_THROW_ON_ERROR);
$kernel->shutdown();

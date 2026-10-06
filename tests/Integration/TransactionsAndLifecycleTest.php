<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ManagerRegistry;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;
use SymPress\DoctrineBundle\Tests\Support\KernelTestCase;
use Symfony\Component\DependencyInjection\ServicesResetterInterface;

final class TransactionsAndLifecycleTest extends KernelTestCase
{
    public function testCascadeAndLazyCollectionsAfterIdentityMapClear(): void
    {
        $this->createSchema();
        $manager = $this->manager();
        $invoice = new Invoice('INV-RELATION');
        $invoice->addLine('Consulting');
        $manager->persist($invoice);
        $manager->flush();
        $id = $invoice->getId();
        $manager->clear();
        $loaded = $manager->find(Invoice::class, $id);
        self::assertInstanceOf(Invoice::class, $loaded);
        $lines = $loaded->getLines();
        self::assertInstanceOf(PersistentCollection::class, $lines);
        self::assertFalse($lines->isInitialized());
        self::assertCount(1, $lines);
        self::assertSame('Consulting', $lines->first()->getDescription());
        self::assertSame($loaded, $lines->first()->getInvoice());
    }

    public function testRollbackClosesManagerAndRegistryCanResetIt(): void
    {
        $this->createSchema();
        $manager = $this->manager();
        try {
            $manager->wrapInTransaction(static function (EntityManagerInterface $manager): void {
                $manager->persist(new Invoice('INV-ROLLBACK'));
                $manager->flush();
                throw new \RuntimeException('Rollback probe.');
            });
        } catch (\RuntimeException $exception) {
            self::assertSame('Rollback probe.', $exception->getMessage());
        }
        self::assertFalse($manager->isOpen());
        self::assertSame(0, (int) $manager->getConnection()->fetchOne('SELECT COUNT(*) FROM enterprise_invoice'));
        $registry = $this->kernel->getContainer()->get(ManagerRegistry::class);
        self::assertInstanceOf(ManagerRegistry::class, $registry);
        $reset = $registry->resetManager();
        self::assertInstanceOf(EntityManagerInterface::class, $reset);
        self::assertTrue($reset->isOpen());
        $reset->persist(new Invoice('INV-RECOVERED'));
        $reset->flush();
        self::assertSame(1, (int) $reset->getConnection()->fetchOne('SELECT COUNT(*) FROM enterprise_invoice'));
    }

    public function testOptimisticLockPreventsOverwritingConcurrentUpdate(): void
    {
        $this->createSchema();
        $manager = $this->manager();
        $invoice = new Invoice('INV-LOCK');
        $manager->persist($invoice);
        $manager->flush();
        $manager->getConnection()->executeStatement(
            'UPDATE enterprise_invoice SET version = version + 1 WHERE id = ?',
            [$invoice->getId()],
        );
        $invoice->rename('INV-STALE');
        $this->expectException(OptimisticLockException::class);
        $manager->flush();
    }

    public function testKernelResetAndShutdownClearManagedObjectsAndConnections(): void
    {
        $this->createSchema();
        $manager = $this->manager();
        $invoice = new Invoice('INV-RESET');
        $manager->persist($invoice);
        $manager->flush();
        // Native workers use the registry, which owns the kernel.reset tag.
        $registry = $this->kernel->getContainer()->get(ManagerRegistry::class);
        self::assertInstanceOf(ManagerRegistry::class, $registry);
        $resetter = $this->kernel->getContainer()->get('services_resetter');
        self::assertInstanceOf(ServicesResetterInterface::class, $resetter);
        $resetter->reset();
        self::assertFalse($manager->contains($invoice));
        $connection = $manager->getConnection();
        $connection->executeQuery('SELECT 1');
        $this->kernel->shutdown();
        self::assertFalse($connection->isConnected());
    }
}

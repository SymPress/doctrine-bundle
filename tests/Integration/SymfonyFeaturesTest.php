<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Integration;

use Composer\Autoload\ClassLoader;
use SymPress\DoctrineBundle\Tests\Fixtures\CreateInvoice;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\StaffMember;
use SymPress\DoctrineBundle\Tests\Fixtures\InvoiceListener;
use SymPress\DoctrineBundle\Tests\Fixtures\QueryLogger;
use SymPress\DoctrineBundle\Tests\Support\KernelTestCase;
use Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyFeaturesTest extends KernelTestCase
{
    public function testNativeSecurityProviderUidAndCustomTypeRoundTrip(): void
    {
        $this->createSchema();
        $manager = $this->manager();
        $staff = new StaffMember('STAFF@EXAMPLE.TEST');
        $id = $staff->getId();
        $manager->persist($staff);
        $manager->flush();
        $manager->clear();
        $provider = $this->kernel->getContainer()->get('test.user_provider');
        self::assertInstanceOf(EntityUserProvider::class, $provider);
        $loaded = $provider->loadUserByIdentifier('staff@example.test');
        self::assertInstanceOf(StaffMember::class, $loaded);
        self::assertSame('staff@example.test', $loaded->getUserIdentifier());
        self::assertSame($id->toRfc4122(), $loaded->getId()->toRfc4122());
        self::assertSame(['ROLE_ENTERPRISE'], $loaded->getRoles());
        self::assertSame($loaded, $provider->refreshUser($loaded));
    }

    public function testDoctrineMessengerCommitsAndRollsBackHandlerWrites(): void
    {
        $this->createSchema();
        $bus = $this->kernel->getContainer()->get('test.message_bus');
        self::assertInstanceOf(MessageBusInterface::class, $bus);
        $bus->dispatch(new CreateInvoice('MSG-COMMIT'));
        $connection = $this->manager()->getConnection();
        self::assertSame(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM enterprise_invoice'));
        try {
            $bus->dispatch(new CreateInvoice('MSG-ROLLBACK', true));
            self::fail('The failing handler must propagate its exception.');
        } catch (HandlerFailedException $exception) {
            self::assertStringContainsString('Messenger rollback probe.', $exception->getMessage());
        }
        self::assertSame(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM enterprise_invoice'));
        self::assertFalse($connection->isTransactionActive());
    }

    public function testConfiguredListenersMiddlewareDqlFiltersAndEntityResolver(): void
    {
        $this->createSchema();
        $manager = $this->manager();
        $visible = new Invoice('VISIBLE');
        $manager->persist($visible);
        $manager->persist(new Invoice('HIDDEN'));
        $manager->flush();
        $container = $this->kernel->getContainer();
        $listener = $container->get(InvoiceListener::class);
        self::assertInstanceOf(InvoiceListener::class, $listener);
        self::assertSame(2, $listener->persistedInvoices);
        $logger = $container->get(QueryLogger::class);
        self::assertInstanceOf(QueryLogger::class, $logger);
        self::assertStringContainsString('INSERT INTO enterprise_invoice', implode("\n", $logger->queries));
        $dql = 'SELECT TEXT_LENGTH(i.reference) FROM ' . Invoice::class . ' i WHERE i.reference = :reference';
        self::assertSame(7, $manager->createQuery($dql)->setParameter('reference', 'VISIBLE')->getSingleScalarResult());
        $manager->getFilters()->enable('invoice_reference')->setParameter('reference', 'VISIBLE');
        self::assertCount(1, $manager->getRepository(Invoice::class)->findAll());
        $manager->getFilters()->disable('invoice_reference');
        self::assertCount(2, $manager->getRepository(Invoice::class)->findAll());
        $resolver = $container->get('test.entity_resolver');
        self::assertInstanceOf(EntityValueResolver::class, $resolver);
        $request = new Request(attributes: ['id' => $visible->getId()]);
        $argument = new ArgumentMetadata('invoice', Invoice::class, false, false, null);
        self::assertSame([$visible], $resolver->resolve($request, $argument));
    }

    public function testNativeMakerGeneratesEntityRepositoryAndMigration(): void
    {
        $loader = new ClassLoader();
        $loader->addPsr4('App\\', $this->cacheRoot . '/project/src');
        $loader->register(true);
        try {
            $console = $this->console();
            $entity = new CommandTester($console->find('make:entity'));
            $entity->setInputs(['']);
            self::assertSame(0, $entity->execute(['name' => 'GeneratedRecord']), $entity->getDisplay());
            $path = $this->cacheRoot . '/project/src/Entity/GeneratedRecord.php';
            self::assertFileExists($path);
            self::assertStringContainsString('#[ORM\\Entity', (string) file_get_contents($path));
            self::assertFileExists($this->cacheRoot . '/project/src/Repository/GeneratedRecordRepository.php');
            $migration = new CommandTester($console->find('make:migration'));
            self::assertSame(0, $migration->execute([]), $migration->getDisplay());
            $files = glob($this->cacheRoot . '/project/migrations/Version*.php');
            self::assertIsArray($files);
            self::assertCount(1, $files);
        } finally {
            $loader->unregister();
        }
    }
}

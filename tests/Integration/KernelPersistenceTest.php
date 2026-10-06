<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;
use SymPress\DoctrineBundle\Tests\Fixtures\InvoiceRepository;
use SymPress\DoctrineBundle\Tests\Support\KernelTestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class KernelPersistenceTest extends KernelTestCase
{
    public function testNativeRepositoriesCrudDqlAndMultipleManagersWithoutWpdb(): void
    {
        self::assertFalse(class_exists('wpdb', false));
        $container = $this->kernel->getContainer();
        $manager = $container->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $manager);
        (new SchemaTool($manager))->createSchema($manager->getMetadataFactory()->getAllMetadata());
        $invoice = new Invoice('INV-001');
        $manager->persist($invoice);
        $manager->flush();
        $id = $invoice->getId();
        self::assertNotNull($id);
        $manager->clear();
        $repository = $container->get(InvoiceRepository::class);
        self::assertInstanceOf(InvoiceRepository::class, $repository);
        self::assertSame($repository, $manager->getRepository(Invoice::class));
        $loaded = $repository->findByReference('INV-001');
        self::assertInstanceOf(Invoice::class, $loaded);
        $loaded->rename('INV-002');
        $manager->flush();
        self::assertSame('INV-002', $manager->getConnection()->fetchOne(
            'SELECT reference FROM enterprise_invoice WHERE id = ?',
            [$id],
        ));
        $registry = $container->get(ManagerRegistry::class);
        self::assertInstanceOf(ManagerRegistry::class, $registry);
        self::assertNotSame($registry->getConnection(), $registry->getConnection('reporting'));
        self::assertSame(['default', 'reporting'], array_keys($registry->getManagers()));
        $manager->remove($loaded);
        $manager->flush();
        self::assertNull($repository->find($id));
    }

    public function testSymfonyUniqueEntityAndEntityTypeUseNativeRegistry(): void
    {
        $container = $this->kernel->getContainer();
        $manager = $container->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $manager);
        (new SchemaTool($manager))->createSchema($manager->getMetadataFactory()->getAllMetadata());
        $invoice = new Invoice('INV-FORM');
        $manager->persist($invoice);
        $manager->flush();
        $validator = $container->get('test.validator');
        self::assertInstanceOf(ValidatorInterface::class, $validator);
        self::assertCount(1, $validator->validate(new Invoice('INV-FORM')));
        self::assertCount(0, $validator->validate(new Invoice('INV-NEW')));
        $forms = $container->get('test.form_factory');
        self::assertInstanceOf(FormFactoryInterface::class, $forms);
        $form = $forms->create(EntityType::class, null, ['class' => Invoice::class, 'choice_label' => 'reference']);
        $form->submit((string) $invoice->getId());
        self::assertTrue($form->isSynchronized());
        self::assertSame($invoice, $form->getData());
    }
}

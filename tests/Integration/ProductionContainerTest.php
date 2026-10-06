<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Integration;

use SymPress\DoctrineBundle\Tests\Fixtures\Entity\Invoice;
use SymPress\DoctrineBundle\Tests\Support\KernelTestCase;
use Symfony\Component\Process\Process;

final class ProductionContainerTest extends KernelTestCase
{
    public function testNewProcessUsesCompiledContainerAndPersistedData(): void
    {
        $this->createSchema();
        $manager = $this->manager();
        $manager->persist(new Invoice('CACHE-RELOAD'));
        $manager->flush();
        self::assertSame(1, $this->kernel->configureCalls);
        $this->kernel->shutdown();
        $process = new Process(
            [PHP_BINARY, dirname(__DIR__) . '/Fixtures/reload.php'],
            env: ['DOCTRINE_RELOAD_ROOT' => $this->cacheRoot],
        );
        $process->mustRun();
        self::assertSame(
            ['configured' => 0, 'reference' => 'CACHE-RELOAD'],
            json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR),
        );
    }
}

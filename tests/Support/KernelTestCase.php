<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Support;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use SymPress\DoctrineBundle\Tests\Fixtures\FixtureKernel;
use SymPress\Kernel\App;
use SymPress\Kernel\Console\ConsoleApplicationFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;

abstract class KernelTestCase extends TestCase
{
    protected FixtureKernel $kernel;
    protected string $cacheRoot;

    protected function setUp(): void
    {
        $this->cacheRoot = sys_get_temp_dir() . '/sympress-doctrine-' . bin2hex(random_bytes(8));
        $this->kernel = $this->createKernel();
        App::new($this->kernel)->boot();
    }

    protected function tearDown(): void
    {
        if (!isset($this->kernel)) {
            return;
        }

        $this->kernel->shutdown();
        (new Filesystem())->remove($this->cacheRoot);
    }

    protected function createKernel(): FixtureKernel
    {
        return new FixtureKernel($this->cacheRoot);
    }

    protected function manager(): EntityManagerInterface
    {
        $manager = $this->kernel->getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $manager);
        return $manager;
    }

    protected function createSchema(): void
    {
        $manager = $this->manager();
        (new SchemaTool($manager))->createSchema($manager->getMetadataFactory()->getAllMetadata());
    }

    protected function console(): Application
    {
        $factory = $this->kernel->getContainer()->get(ConsoleApplicationFactory::class);
        self::assertInstanceOf(ConsoleApplicationFactory::class, $factory);
        return $factory->create();
    }

    protected function restartKernel(): void
    {
        $this->kernel->shutdown();
        $this->kernel = $this->createKernel();
        App::new($this->kernel)->boot();
    }
}

<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle;

/** Supplies only the discovery metadata required by the SymPress kernel. */
trait KernelBundleTrait
{
    public function id(): string
    {
        return static::class;
    }

    public function path(): string
    {
        return dirname(__DIR__);
    }

    public function configPath(): ?string
    {
        return null;
    }

    /** @return list<string> */
    public function configPaths(): array
    {
        return [];
    }

    public function translationPath(): ?string
    {
        return null;
    }
}

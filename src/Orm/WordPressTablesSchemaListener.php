<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Orm;

use Doctrine\DBAL\Schema\NamedObject;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use SymPress\DoctrineBundle\Dbal\WordPressTablesSchemaFilter;

/**
 * Removes WordPress tables from the schema the ORM generates from entity mappings. The connection filter alone
 * does not cover mapped tables: SchemaTool admits every mapped table to its comparison, so schema:update would
 * otherwise rebuild a mapped wp_users and drop its unmapped columns.
 */
final class WordPressTablesSchemaListener
{
    public function __construct(private readonly WordPressTablesSchemaFilter $filter)
    {
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();
        foreach ($schema->getTables() as $table) {
            // Tables implement NamedObject from DBAL 4.4; older versions only offer getName().
            /** @phpstan-ignore method.internalClass */
            $name = $table instanceof NamedObject ? $table->getObjectName()->toString() : $table->getName();
            if (($this->filter)($name)) {
                continue;
            }

            // shortcut: deprecated since DBAL 4.5 like in ORM's own SchemaTool; switch to Schema::edit() for 5.0.
            /** @phpstan-ignore method.deprecated */
            $schema->dropTable($name);
        }
    }
}

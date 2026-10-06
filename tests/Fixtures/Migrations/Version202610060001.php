<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Tests\Fixtures\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version202610060001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the disposable integration audit table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE enterprise_audit (id INT NOT NULL, message VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE enterprise_audit');
    }
}

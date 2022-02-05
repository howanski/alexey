<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220205193010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add option to show/hide money node on new transfer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE money_node ADD selectable TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE money_node DROP selectable');
    }
}

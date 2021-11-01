<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211101212630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add group to money node';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE money_node ADD node_group SMALLINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE money_node DROP node_group');
    }
}

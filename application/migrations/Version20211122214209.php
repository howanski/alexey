<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211122214209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email address to User entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD email VARCHAR(254) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP email');
    }
}

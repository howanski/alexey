<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260724134422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_read flag to assistant call';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_call ADD is_read TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_call DROP is_read');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811125528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Per-chat error logging';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_call ADD error_count INT NOT NULL DEFAULT 0, ADD last_error LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('UPDATE assistant_call SET last_error=\'a:0:{}\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_call DROP error_count, DROP last_error');
    }
}

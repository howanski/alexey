<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260719141222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add used tools memory to assistant requests';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_call ADD tools LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_call DROP tools');
    }
}

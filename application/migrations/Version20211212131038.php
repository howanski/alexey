<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211212131038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'bigger db column for reddit post thumbnail';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reddit_post CHANGE thumbnail thumbnail LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reddit_post CHANGE thumbnail thumbnail VARCHAR(2048) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}

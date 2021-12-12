<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211212105128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'reddit crawler upgrade';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reddit_channel DROP coverage');
        $this->addSql('ALTER TABLE reddit_post CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE thumbnail thumbnail VARCHAR(2048) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reddit_channel ADD coverage LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE reddit_post CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE thumbnail thumbnail VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}

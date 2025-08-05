<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250805075504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add last_seen column to reddit_banned_poster';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reddit_banned_poster ADD last_seen DATE NULL');
        $this->addSql('UPDATE reddit_banned_poster SET last_seen = NOW() WHERE last_seen IS NULL');
        $this->addSql('ALTER TABLE reddit_banned_poster MODIFY COLUMN last_seen DATE NOT NULL;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reddit_banned_poster DROP last_seen');
    }
}

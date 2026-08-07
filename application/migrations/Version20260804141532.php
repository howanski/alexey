<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260804141532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Assistant - add context sliding window limit';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_recurring_message ADD max_messages_to_send_at_once INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_recurring_message DROP max_messages_to_send_at_once');
    }
}

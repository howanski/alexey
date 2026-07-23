<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260722202104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Expand assistant_recurring_message';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_recurring_message ADD name VARCHAR(255) NOT NULL, ADD model VARCHAR(255) NOT NULL');

        // removed const MODEL from application/src/Model/AssistantSettings.php
        $this->addSql('DELETE FROM simple_setting WHERE setting_key IN (\'ASSISTANT_DEFAULT_MODEL\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_recurring_message DROP name, DROP model');
    }
}

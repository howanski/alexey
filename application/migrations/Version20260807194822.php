<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260807194822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'AssistantMemory Entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE assistant_memory (id BIGINT AUTO_INCREMENT NOT NULL, assistant_id INT NOT NULL, user_content LONGTEXT NOT NULL, assistant_content LONGTEXT NOT NULL, status SMALLINT NOT NULL, created_at DATETIME NOT NULL, user_title VARCHAR(255) NOT NULL, assistant_title VARCHAR(255) NOT NULL, INDEX IDX_62587511E05387EF (assistant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE assistant_memory ADD CONSTRAINT FK_62587511E05387EF FOREIGN KEY (assistant_id) REFERENCES assistant_recurring_message (id)');
        $this->addSql('ALTER TABLE assistant_recurring_message ADD max_memories INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_memory DROP FOREIGN KEY FK_62587511E05387EF');
        $this->addSql('DROP TABLE assistant_memory');
        $this->addSql('ALTER TABLE assistant_recurring_message DROP max_memories');
    }
}

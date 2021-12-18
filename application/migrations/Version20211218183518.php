<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211218183518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ApiDevice table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE api_device (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, secret VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, last_request DATETIME NOT NULL, permissions LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_8FD8F10EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE api_device ADD CONSTRAINT FK_8FD8F10EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE api_device');
    }
}

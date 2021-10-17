<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211017123352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add MoneyNode entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE money_node (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, node_type SMALLINT NOT NULL, INDEX IDX_6AF34B42A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE money_node ADD CONSTRAINT FK_6AF34B42A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE money_node');
    }
}

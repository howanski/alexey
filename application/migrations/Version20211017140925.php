<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211017140925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create MoneyTransfer Entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE money_transfer (id INT AUTO_INCREMENT NOT NULL, source_node_id INT NOT NULL, target_node_id INT NOT NULL, operation_date DATE NOT NULL, amount INT NOT NULL, exchange_rate DOUBLE PRECISION NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_A15E50EEFD227BF (source_node_id), INDEX IDX_A15E50EE8D6526BC (target_node_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EEFD227BF FOREIGN KEY (source_node_id) REFERENCES money_node (id)');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EE8D6526BC FOREIGN KEY (target_node_id) REFERENCES money_node (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE money_transfer');
    }
}

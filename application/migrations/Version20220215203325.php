<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220215203325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add concept of currency';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, code VARCHAR(3) NOT NULL, is_main TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_6956883FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE currency ADD CONSTRAINT FK_6956883FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE money_node ADD currency_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE money_node ADD CONSTRAINT FK_6AF34B4238248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('CREATE INDEX IDX_6AF34B4238248176 ON money_node (currency_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE money_node DROP FOREIGN KEY FK_6AF34B4238248176');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP INDEX IDX_6AF34B4238248176 ON money_node');
        $this->addSql('ALTER TABLE money_node DROP currency_id');
    }
}

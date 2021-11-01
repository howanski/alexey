<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211101145627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Money Transfer - add User';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE money_transfer ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A15E50EEA76ED395 ON money_transfer (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE money_transfer DROP FOREIGN KEY FK_A15E50EEA76ED395');
        $this->addSql('DROP INDEX IDX_A15E50EEA76ED395 ON money_transfer');
        $this->addSql('ALTER TABLE money_transfer DROP user_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016141028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Storage module - add minimal quantity for StorageItem';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE storage_item ADD minimal_quantity INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE storage_item DROP minimal_quantity');
    }
}

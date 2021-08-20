<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210801084810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'NetworkMachine Entity - added fields for WOL functionality';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE network_machine ADD mac_address VARCHAR(23) DEFAULT NULL, ADD wake_destination VARCHAR(23) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE network_machine DROP mac_address, DROP wake_destination');
    }
}

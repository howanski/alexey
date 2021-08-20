<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210801164834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'NetworkMachine Entity - added flag "Show on dashboard"';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE network_machine ADD show_on_dashboard TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE network_machine DROP show_on_dashboard');
    }
}

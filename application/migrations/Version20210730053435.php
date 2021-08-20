<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210730053435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create NetworkMachine Entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE network_machine (id INT AUTO_INCREMENT NOT NULL, uri VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, status SMALLINT NOT NULL, last_seen DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE network_machine');
    }
}

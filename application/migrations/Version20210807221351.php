<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210807221351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'New Entity - SimpleSetting';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE simple_setting (id INT AUTO_INCREMENT NOT NULL, setting_key VARCHAR(50) NOT NULL, setting_value VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE simple_setting');
    }
}

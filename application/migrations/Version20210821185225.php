<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210821185225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Simple Cache Entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE simple_cache (id INT AUTO_INCREMENT NOT NULL, cache_key VARCHAR(50) NOT NULL, cache_data JSON NOT NULL, valid_to DATETIME NOT NULL, UNIQUE INDEX UNIQ_F35581AD763247D7 (cache_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE simple_cache');
    }
}

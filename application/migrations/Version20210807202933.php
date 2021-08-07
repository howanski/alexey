<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210807202933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create NetworkStatistic entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE network_statistic (id BIGINT AUTO_INCREMENT NOT NULL, probing_time DATETIME NOT NULL, billing_frame_start DATETIME NOT NULL, billing_frame_end DATETIME NOT NULL, data_uploaded_in_frame BIGINT NOT NULL, data_downloaded_in_frame BIGINT NOT NULL, billing_frame_data_limit BIGINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE network_statistic');
    }
}

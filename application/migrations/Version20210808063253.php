<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210808063253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Redundant data on NetworkStatistic Enity extracted';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE network_statistic_time_frame (id INT AUTO_INCREMENT NOT NULL, billing_frame_start DATETIME NOT NULL, billing_frame_end DATETIME NOT NULL, billing_frame_data_limit BIGINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE network_statistic ADD time_frame_id INT NOT NULL, DROP billing_frame_start, DROP billing_frame_end, DROP billing_frame_data_limit');
        $this->addSql('ALTER TABLE network_statistic ADD CONSTRAINT FK_E33E26759B26808B FOREIGN KEY (time_frame_id) REFERENCES network_statistic_time_frame (id)');
        $this->addSql('CREATE INDEX IDX_E33E26759B26808B ON network_statistic (time_frame_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE network_statistic DROP FOREIGN KEY FK_E33E26759B26808B');
        $this->addSql('DROP TABLE network_statistic_time_frame');
        $this->addSql('DROP INDEX IDX_E33E26759B26808B ON network_statistic');
        $this->addSql('ALTER TABLE network_statistic ADD billing_frame_start DATETIME NOT NULL, ADD billing_frame_end DATETIME NOT NULL, ADD billing_frame_data_limit BIGINT NOT NULL, DROP time_frame_id');
    }
}

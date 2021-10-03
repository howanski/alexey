<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211003200855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CronJob - entity to store information about repetitive jobs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cron_job (id INT AUTO_INCREMENT NOT NULL, last_run DATETIME DEFAULT NULL, run_every INT NOT NULL, is_active TINYINT(1) NOT NULL, job_type VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_8E6EB8EB122168 (job_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cron_job');
    }
}

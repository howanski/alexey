<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704081824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop Reddit Support';
    }

    public function up(Schema $schema): void
    {
        // drop reddit tables
        $this->addSql('ALTER TABLE reddit_banned_poster DROP FOREIGN KEY FK_B17B15FFA76ED395');
        $this->addSql('ALTER TABLE reddit_channel DROP FOREIGN KEY FK_3111D89F89E4AAEE');
        $this->addSql('ALTER TABLE reddit_channel DROP FOREIGN KEY FK_3111D89FA76ED395');
        $this->addSql('ALTER TABLE reddit_channel_group DROP FOREIGN KEY FK_7F41870AA76ED395');
        $this->addSql('ALTER TABLE reddit_post DROP FOREIGN KEY FK_E039466972F5A1AA');
        $this->addSql('DROP TABLE reddit_banned_poster');
        $this->addSql('DROP TABLE reddit_channel');
        $this->addSql('DROP TABLE reddit_channel_group');
        $this->addSql('DROP TABLE reddit_post');

        // clean up orphaned simple_settings rows
        // strings are consts from deleted application/src/Service/RedditReader.php
        $this->addSql('DELETE FROM simple_setting WHERE setting_key IN (\'REDDIT_USERNAME\', \'REDDIT_EMPTY_STREAM_AUTOHIDE\')');

        // clean up orphaned cron_job rows
        // strings are deleted consts from application/src/Message/AsyncJob.php
        $this->addSql('DELETE FROM cron_job WHERE job_type IN (\'update_crawler\', \'update_crawler_channel\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reddit_banned_poster (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_seen DATE NOT NULL, INDEX IDX_B17B15FFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reddit_channel (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, channel_group_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_fetch DATETIME NOT NULL, INDEX IDX_3111D89F89E4AAEE (channel_group_id), INDEX IDX_3111D89FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reddit_channel_group (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, order_number INT NOT NULL, INDEX IDX_7F41870AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reddit_post (id BIGINT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, uri VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, seen TINYINT(1) NOT NULL, thumbnail LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, published DATETIME NOT NULL, touched DATETIME NOT NULL, user VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_E039466972F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE reddit_banned_poster ADD CONSTRAINT FK_B17B15FFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reddit_channel ADD CONSTRAINT FK_3111D89F89E4AAEE FOREIGN KEY (channel_group_id) REFERENCES reddit_channel_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reddit_channel ADD CONSTRAINT FK_3111D89FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reddit_channel_group ADD CONSTRAINT FK_7F41870AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reddit_post ADD CONSTRAINT FK_E039466972F5A1AA FOREIGN KEY (channel_id) REFERENCES reddit_channel (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}

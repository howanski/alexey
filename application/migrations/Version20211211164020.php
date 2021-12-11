<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211211164020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tables for reddit crawler';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reddit_channel (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, last_fetch DATETIME NOT NULL, coverage LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', nsfw TINYINT(1) NOT NULL, INDEX IDX_3111D89FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reddit_post (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, uri VARCHAR(255) NOT NULL, seen TINYINT(1) NOT NULL, thumbnail VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, published DATETIME NOT NULL, touched DATETIME NOT NULL, INDEX IDX_E039466972F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reddit_channel ADD CONSTRAINT FK_3111D89FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reddit_post ADD CONSTRAINT FK_E039466972F5A1AA FOREIGN KEY (channel_id) REFERENCES reddit_channel (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reddit_post DROP FOREIGN KEY FK_E039466972F5A1AA');
        $this->addSql('DROP TABLE reddit_channel');
        $this->addSql('DROP TABLE reddit_post');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230109113139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add banned users for Reddit Crawler';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reddit_banned_poster (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, username VARCHAR(255) NOT NULL, INDEX IDX_B17B15FFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reddit_banned_poster ADD CONSTRAINT FK_B17B15FFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE reddit_banned_poster');
    }
}

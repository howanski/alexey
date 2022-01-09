<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220109093702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added Reddit Channel grouping';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reddit_channel_group (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, order_number INT NOT NULL, INDEX IDX_7F41870AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reddit_channel_group ADD CONSTRAINT FK_7F41870AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reddit_channel ADD channel_group_id INT DEFAULT NULL, DROP nsfw');
        $this->addSql('ALTER TABLE reddit_channel ADD CONSTRAINT FK_3111D89F89E4AAEE FOREIGN KEY (channel_group_id) REFERENCES reddit_channel_group (id)');
        $this->addSql('CREATE INDEX IDX_3111D89F89E4AAEE ON reddit_channel (channel_group_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reddit_channel DROP FOREIGN KEY FK_3111D89F89E4AAEE');
        $this->addSql('DROP TABLE reddit_channel_group');
        $this->addSql('DROP INDEX IDX_3111D89F89E4AAEE ON reddit_channel');
        $this->addSql('ALTER TABLE reddit_channel ADD nsfw TINYINT(1) NOT NULL, DROP channel_group_id');
    }
}

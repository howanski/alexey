<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211221175530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'reddit_post - store user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reddit_post ADD user VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reddit_post DROP user');
    }
}

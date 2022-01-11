<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220111181253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'User - store otp';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD otp VARCHAR(15) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP otp');
    }
}

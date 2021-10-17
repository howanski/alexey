<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211017102444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add User connection to SimpleCache and SimpleSetting';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE simple_cache ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE simple_cache ADD CONSTRAINT FK_F35581ADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F35581ADA76ED395 ON simple_cache (user_id)');
        $this->addSql('ALTER TABLE simple_setting ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE simple_setting ADD CONSTRAINT FK_D53DA663A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D53DA663A76ED395 ON simple_setting (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE simple_cache DROP FOREIGN KEY FK_F35581ADA76ED395');
        $this->addSql('DROP INDEX IDX_F35581ADA76ED395 ON simple_cache');
        $this->addSql('ALTER TABLE simple_cache DROP user_id');
        $this->addSql('ALTER TABLE simple_setting DROP FOREIGN KEY FK_D53DA663A76ED395');
        $this->addSql('DROP INDEX IDX_D53DA663A76ED395 ON simple_setting');
        $this->addSql('ALTER TABLE simple_setting DROP user_id');
    }
}

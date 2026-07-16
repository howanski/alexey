<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716185825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Basic AI chat entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE assistant_call (id BIGINT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, parent_id BIGINT DEFAULT NULL, root_id BIGINT DEFAULT NULL, system_message_id INT DEFAULT NULL, user_query LONGTEXT NOT NULL, assistant_response LONGTEXT DEFAULT NULL, last_status_change DATETIME NOT NULL, metadata LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', status SMALLINT NOT NULL, type SMALLINT NOT NULL, INDEX IDX_FD9205BA76ED395 (user_id), INDEX IDX_FD9205B727ACA70 (parent_id), INDEX IDX_FD9205B79066886 (root_id), INDEX IDX_FD9205BE6A90310 (system_message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE assistant_recurring_message (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, message LONGTEXT NOT NULL, type SMALLINT NOT NULL, priority INT NOT NULL, INDEX IDX_CD0E2ECFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE assistant_call ADD CONSTRAINT FK_FD9205BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE assistant_call ADD CONSTRAINT FK_FD9205B727ACA70 FOREIGN KEY (parent_id) REFERENCES assistant_call (id)');
        $this->addSql('ALTER TABLE assistant_call ADD CONSTRAINT FK_FD9205B79066886 FOREIGN KEY (root_id) REFERENCES assistant_call (id)');
        $this->addSql('ALTER TABLE assistant_call ADD CONSTRAINT FK_FD9205BE6A90310 FOREIGN KEY (system_message_id) REFERENCES assistant_recurring_message (id)');
        $this->addSql('ALTER TABLE assistant_recurring_message ADD CONSTRAINT FK_CD0E2ECFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assistant_call DROP FOREIGN KEY FK_FD9205BA76ED395');
        $this->addSql('ALTER TABLE assistant_call DROP FOREIGN KEY FK_FD9205B727ACA70');
        $this->addSql('ALTER TABLE assistant_call DROP FOREIGN KEY FK_FD9205B79066886');
        $this->addSql('ALTER TABLE assistant_call DROP FOREIGN KEY FK_FD9205BE6A90310');
        $this->addSql('ALTER TABLE assistant_recurring_message DROP FOREIGN KEY FK_CD0E2ECFA76ED395');
        $this->addSql('DROP TABLE assistant_call');
        $this->addSql('DROP TABLE assistant_recurring_message');
    }
}

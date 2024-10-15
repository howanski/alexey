<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015152403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Storage module database changes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE storage_item (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, unit_of_measure VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE storage_item_attribute (id INT AUTO_INCREMENT NOT NULL, storage_item_id INT NOT NULL, attribute_type_id INT NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_FA5E1C666553613C (storage_item_id), INDEX IDX_FA5E1C664ED0D557 (attribute_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE storage_item_attribute_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE storage_item_stack (id INT AUTO_INCREMENT NOT NULL, storage_space_id INT NOT NULL, storage_item_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_FB586634809C6F07 (storage_space_id), INDEX IDX_FB5866346553613C (storage_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE storage_space (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_44A6081AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE storage_item_attribute ADD CONSTRAINT FK_FA5E1C666553613C FOREIGN KEY (storage_item_id) REFERENCES storage_item (id)');
        $this->addSql('ALTER TABLE storage_item_attribute ADD CONSTRAINT FK_FA5E1C664ED0D557 FOREIGN KEY (attribute_type_id) REFERENCES storage_item_attribute_type (id)');
        $this->addSql('ALTER TABLE storage_item_stack ADD CONSTRAINT FK_FB586634809C6F07 FOREIGN KEY (storage_space_id) REFERENCES storage_space (id)');
        $this->addSql('ALTER TABLE storage_item_stack ADD CONSTRAINT FK_FB5866346553613C FOREIGN KEY (storage_item_id) REFERENCES storage_item (id)');
        $this->addSql('ALTER TABLE storage_space ADD CONSTRAINT FK_44A6081AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE storage_item_attribute DROP FOREIGN KEY FK_FA5E1C666553613C');
        $this->addSql('ALTER TABLE storage_item_stack DROP FOREIGN KEY FK_FB5866346553613C');
        $this->addSql('ALTER TABLE storage_item_attribute DROP FOREIGN KEY FK_FA5E1C664ED0D557');
        $this->addSql('ALTER TABLE storage_item_stack DROP FOREIGN KEY FK_FB586634809C6F07');
        $this->addSql('DROP TABLE storage_item');
        $this->addSql('DROP TABLE storage_item_attribute');
        $this->addSql('DROP TABLE storage_item_attribute_type');
        $this->addSql('DROP TABLE storage_item_stack');
        $this->addSql('DROP TABLE storage_space');
    }
}

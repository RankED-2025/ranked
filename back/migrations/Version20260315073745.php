<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260315073745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE zamn_entity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE test_entity ADD zamn_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE test_entity ADD CONSTRAINT FK_99F14346A1EA772C FOREIGN KEY (zamn_entity_id) REFERENCES zamn_entity (id)');
        $this->addSql('CREATE INDEX IDX_99F14346A1EA772C ON test_entity (zamn_entity_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE test_entity DROP FOREIGN KEY FK_99F14346A1EA772C');
        $this->addSql('DROP TABLE zamn_entity');
        $this->addSql('DROP INDEX IDX_99F14346A1EA772C ON test_entity');
        $this->addSql('ALTER TABLE test_entity DROP zamn_entity_id');
    }
}

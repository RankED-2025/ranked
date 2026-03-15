<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260315073112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE test_entity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, u_null VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE refresh_token ADD refresh_token VARCHAR(128) NOT NULL, ADD username VARCHAR(255) NOT NULL, ADD valid DATETIME NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74F2195C74F2195 ON refresh_token (refresh_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE test_entity');
        $this->addSql('DROP INDEX UNIQ_C74F2195C74F2195 ON refresh_token');
        $this->addSql('ALTER TABLE refresh_token DROP refresh_token, DROP username, DROP valid, CHANGE id id VARCHAR(255) NOT NULL');
    }
}

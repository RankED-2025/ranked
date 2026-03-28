<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326100417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE difficulte (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cours ADD difficulte_id INT NOT NULL, ADD titre VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CE6357589 FOREIGN KEY (difficulte_id) REFERENCES difficulte (id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9CE6357589 ON cours (difficulte_id)');
        $this->addSql('ALTER TABLE progression ADD percentage INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE refresh_token ADD refresh_token VARCHAR(128) NOT NULL, ADD username VARCHAR(255) NOT NULL, ADD valid DATETIME NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74F2195C74F2195 ON refresh_token (refresh_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CE6357589');
        $this->addSql('DROP TABLE difficulte');
        $this->addSql('DROP INDEX IDX_FDCA8C9CE6357589 ON cours');
        $this->addSql('ALTER TABLE cours DROP difficulte_id, DROP titre, DROP description');
        $this->addSql('ALTER TABLE progression DROP percentage');
        $this->addSql('DROP INDEX UNIQ_C74F2195C74F2195 ON refresh_token');
        $this->addSql('ALTER TABLE refresh_token DROP refresh_token, DROP username, DROP valid, CHANGE id id VARCHAR(255) NOT NULL');
    }
}

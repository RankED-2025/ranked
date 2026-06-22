<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610131358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added activite_progression table to track eleve\'s progressions on activities.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activite_progression (id INT AUTO_INCREMENT NOT NULL, eleve_id INT NOT NULL, activite_id INT NOT NULL, completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F581255EA6CC7B2 (eleve_id), INDEX IDX_F581255E9B0F88B1 (activite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activite_progression ADD CONSTRAINT FK_F581255EA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleve (id)');
        $this->addSql('ALTER TABLE activite_progression ADD CONSTRAINT FK_F581255E9B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite_progression DROP FOREIGN KEY FK_F581255EA6CC7B2');
        $this->addSql('ALTER TABLE activite_progression DROP FOREIGN KEY FK_F581255E9B0F88B1');
        $this->addSql('DROP TABLE activite_progression');
    }
}

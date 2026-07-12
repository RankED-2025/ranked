<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260712132113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nullable classe_id to progression, so a course assignment is scoped to the class it was assigned through.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE progression ADD classe_id INT DEFAULT NULL');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              progression
            ADD
              CONSTRAINT FK_D5B250738F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)
        SQL);
        $this->addSql('CREATE INDEX IDX_D5B250738F5EA509 ON progression (classe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE progression DROP FOREIGN KEY FK_D5B250738F5EA509');
        $this->addSql('DROP INDEX IDX_D5B250738F5EA509 ON progression');
        $this->addSql('ALTER TABLE progression DROP classe_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220210736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE refresh_token (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_REFRESH_TOKEN_TOKEN (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE password_reset_token RENAME INDEX uniq_d53d977e5f37a13b TO UNIQ_6B7BA4B65F37A13B');
        $this->addSql('ALTER TABLE password_reset_token RENAME INDEX idx_d53d977ea76ed395 TO IDX_6B7BA4B6A76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('ALTER TABLE password_reset_token RENAME INDEX uniq_6b7ba4b65f37a13b TO UNIQ_D53D977E5F37A13B');
        $this->addSql('ALTER TABLE password_reset_token RENAME INDEX idx_6b7ba4b6a76ed395 TO IDX_D53D977EA76ED395');
    }
}

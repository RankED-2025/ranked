<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260220000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add password_reset_token table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE password_reset_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_D53D977E5F37A13B (token), INDEX IDX_D53D977EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_D53D977EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE password_reset_token DROP FOREIGN KEY FK_D53D977EA76ED395');
        $this->addSql('DROP TABLE password_reset_token');
    }
}

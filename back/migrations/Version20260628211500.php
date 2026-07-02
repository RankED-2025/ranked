<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260628211500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added score, total and earnedPts columns to activite_progression to store quiz results.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activite_progression ADD score INT DEFAULT NULL, ADD total INT DEFAULT NULL, ADD earned_pts INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activite_progression DROP score, DROP total, DROP earned_pts');
    }
}

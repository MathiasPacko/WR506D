<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251201142615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add online field to movie table';
    }

    public function up(Schema $schema): void
    {
        // Add online field to existing movie table
        $this->addSql('ALTER TABLE movie ADD online TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove online field from movie table
        $this->addSql('ALTER TABLE movie DROP online');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250311083053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commentaries RENAME INDEX idx_1cac12caa76ed395 TO IDX_4ED55CCBA76ED395');
        $this->addSql('ALTER TABLE commentaries RENAME INDEX idx_1cac12ca4b89032c TO IDX_4ED55CCB4B89032C');
        $this->addSql('ALTER TABLE exchange_rate CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE posts ADD image VARCHAR(255) DEFAULT NULL AFTER text, ADD thumbnail VARCHAR(255) DEFAULT NULL AFTER image');
        $this->addSql('ALTER TABLE users ADD avatar VARCHAR(255) DEFAULT NULL AFTER email, ADD thumbnail VARCHAR(255) DEFAULT NULL AFTER avatar');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commentaries RENAME INDEX idx_4ed55ccba76ed395 TO IDX_1CAC12CAA76ED395');
        $this->addSql('ALTER TABLE commentaries RENAME INDEX idx_4ed55ccb4b89032c TO IDX_1CAC12CA4B89032C');
        $this->addSql('ALTER TABLE posts DROP image, DROP thumbnail');
        $this->addSql('ALTER TABLE exchange_rate CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE users DROP avatar, DROP thumbnail');
    }
}

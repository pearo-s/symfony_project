<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220054746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX email ON user');
        $this->addSql('DROP INDEX username ON user');
        $this->addSql('ALTER TABLE user ADD dob DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP dob');
        $this->addSql('CREATE UNIQUE INDEX email ON `user` (email)');
        $this->addSql('CREATE UNIQUE INDEX username ON `user` (username)');
    }
}

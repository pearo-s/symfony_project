<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250318093428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE commentary_likes (id INT AUTO_INCREMENT NOT NULL, 
                                                            user_id INT NOT NULL, 
                                                            commentary_id INT NOT NULL, 
                                                            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                                                            INDEX IDX_7B20A778A76ED395 (user_id), 
                                                            INDEX IDX_7B20A7785DED49AA (commentary_id), 
                                                            PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE commentary_likes ADD CONSTRAINT FK_7B20A778A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE commentary_likes ADD CONSTRAINT FK_7B20A7785DED49AA FOREIGN KEY (commentary_id) REFERENCES commentaries (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commentary_likes DROP FOREIGN KEY FK_7B20A778A76ED395');
        $this->addSql('ALTER TABLE commentary_likes DROP FOREIGN KEY FK_7B20A7785DED49AA');
        $this->addSql('DROP TABLE commentary_likes');
    }
}

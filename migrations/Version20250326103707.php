<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326103707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_category (id INT AUTO_INCREMENT NOT NULL, 
                                                            name VARCHAR(255) NOT NULL, 
                                                            PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE product_image (id INT AUTO_INCREMENT NOT NULL, 
                                                        product_id INT NOT NULL, 
                                                        path VARCHAR(255) NOT NULL, 
                                                        INDEX IDX_64617F034584665A (product_id), 
                                                        PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, 
                                                    category_id INT NOT NULL, 
                                                    name VARCHAR(255) NOT NULL, 
                                                    description LONGTEXT DEFAULT NULL, 
                                                    price INT NOT NULL, 
                                                    colour VARCHAR(50) DEFAULT NULL, 
                                                    quantity INT NOT NULL, 
                                                    created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                                                    updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                                                    INDEX IDX_B3BA5A5A12469DE2 (category_id), 
                                                    PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A12469DE2 FOREIGN KEY (category_id) REFERENCES product_category (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A12469DE2');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE product_image');
        $this->addSql('DROP TABLE products');
    }
}

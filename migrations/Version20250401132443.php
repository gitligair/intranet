<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401132443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ordinateur (id INT AUTO_INCREMENT NOT NULL, modele VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, sous_categorie VARCHAR(255) NOT NULL, processeur VARCHAR(255) DEFAULT NULL, os VARCHAR(255) NOT NULL, ram DOUBLE PRECISION NOT NULL, stockage DOUBLE PRECISION NOT NULL, logiciels LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', identifiant VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ordinateur');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401120232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE materiel (id INT AUTO_INCREMENT NOT NULL, type_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, localisation_id INT DEFAULT NULL, utilisateur_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_stock TINYINT(1) NOT NULL, buy_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', prix DOUBLE PRECISION DEFAULT NULL, INDEX IDX_18D2B091C54C8C93 (type_id), INDEX IDX_18D2B091B03A8386 (created_by_id), INDEX IDX_18D2B091C68BE09C (localisation_id), INDEX IDX_18D2B091FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE materiel ADD CONSTRAINT FK_18D2B091C54C8C93 FOREIGN KEY (type_id) REFERENCES type_materiel (id)');
        $this->addSql('ALTER TABLE materiel ADD CONSTRAINT FK_18D2B091B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE materiel ADD CONSTRAINT FK_18D2B091C68BE09C FOREIGN KEY (localisation_id) REFERENCES bureau (id)');
        $this->addSql('ALTER TABLE materiel ADD CONSTRAINT FK_18D2B091FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE materiel DROP FOREIGN KEY FK_18D2B091C54C8C93');
        $this->addSql('ALTER TABLE materiel DROP FOREIGN KEY FK_18D2B091B03A8386');
        $this->addSql('ALTER TABLE materiel DROP FOREIGN KEY FK_18D2B091C68BE09C');
        $this->addSql('ALTER TABLE materiel DROP FOREIGN KEY FK_18D2B091FB88E14F');
        $this->addSql('DROP TABLE materiel');
    }
}

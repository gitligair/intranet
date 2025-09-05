<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828151838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cotech_vacarm_materiel_bd (id INT AUTO_INCREMENT NOT NULL, materiel_id INT NOT NULL, base VARCHAR(255) NOT NULL, host VARCHAR(255) NOT NULL, user VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, port INT NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_1E8BE20616880AAF (materiel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cotech_vacarm_materiel_bd ADD CONSTRAINT FK_1E8BE20616880AAF FOREIGN KEY (materiel_id) REFERENCES cotech_vacarm_materiel (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cotech_vacarm_materiel_bd DROP FOREIGN KEY FK_1E8BE20616880AAF');
        $this->addSql('DROP TABLE cotech_vacarm_materiel_bd');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250317140353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE poles (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, identifiant VARCHAR(255) NOT NULL, added_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_online TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE poles_user (poles_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_53F15B0D7AE256B9 (poles_id), INDEX IDX_53F15B0DA76ED395 (user_id), PRIMARY KEY(poles_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE poles_user ADD CONSTRAINT FK_53F15B0D7AE256B9 FOREIGN KEY (poles_id) REFERENCES poles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE poles_user ADD CONSTRAINT FK_53F15B0DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE poles_user DROP FOREIGN KEY FK_53F15B0D7AE256B9');
        $this->addSql('ALTER TABLE poles_user DROP FOREIGN KEY FK_53F15B0DA76ED395');
        $this->addSql('DROP TABLE poles');
        $this->addSql('DROP TABLE poles_user');
    }
}
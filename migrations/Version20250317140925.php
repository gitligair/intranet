<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250317140925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE poles ADD services_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE poles ADD CONSTRAINT FK_CCFE7DA7AEF5A6C1 FOREIGN KEY (services_id) REFERENCES services (id)');
        $this->addSql('CREATE INDEX IDX_CCFE7DA7AEF5A6C1 ON poles (services_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP password_');
        $this->addSql('ALTER TABLE poles DROP FOREIGN KEY FK_CCFE7DA7AEF5A6C1');
        $this->addSql('DROP INDEX IDX_CCFE7DA7AEF5A6C1 ON poles');
    }
}
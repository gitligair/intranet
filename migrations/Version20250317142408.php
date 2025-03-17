<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250317142408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE poles ADD processus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE poles ADD CONSTRAINT FK_CCFE7DA7A55629DC FOREIGN KEY (processus_id) REFERENCES processus (id)');
        $this->addSql('CREATE INDEX IDX_CCFE7DA7A55629DC ON poles (processus_id)');
        $this->addSql('ALTER TABLE services ADD processus_id INT DEFAULT NULL, ADD responsable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE services ADD CONSTRAINT FK_7332E169A55629DC FOREIGN KEY (processus_id) REFERENCES processus (id)');
        $this->addSql('ALTER TABLE services ADD CONSTRAINT FK_7332E16953C59D72 FOREIGN KEY (responsable_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7332E169A55629DC ON services (processus_id)');
        $this->addSql('CREATE INDEX IDX_7332E16953C59D72 ON services (responsable_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP password_');
        $this->addSql('ALTER TABLE services DROP FOREIGN KEY FK_7332E169A55629DC');
        $this->addSql('ALTER TABLE services DROP FOREIGN KEY FK_7332E16953C59D72');
        $this->addSql('DROP INDEX IDX_7332E169A55629DC ON services');
        $this->addSql('DROP INDEX IDX_7332E16953C59D72 ON services');
        $this->addSql('ALTER TABLE services DROP processus_id, DROP responsable_id');
        $this->addSql('ALTER TABLE poles DROP FOREIGN KEY FK_CCFE7DA7A55629DC');
        $this->addSql('DROP INDEX IDX_CCFE7DA7A55629DC ON poles');
    }
}
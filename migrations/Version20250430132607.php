<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430132607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ecran CHANGE id id INT NOT NULL, CHANGE connecteurs connecteurs JSON DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ecran ADD CONSTRAINT FK_3FFAFD4ABF396750 FOREIGN KEY (id) REFERENCES materiel (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE materiel ADD typage VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ordinateur CHANGE logiciels logiciels JSON DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ordinateur CHANGE logiciels logiciels LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE materiel DROP typage
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ecran DROP FOREIGN KEY FK_3FFAFD4ABF396750
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ecran CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE connecteurs connecteurs LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)'
        SQL);
    }
}

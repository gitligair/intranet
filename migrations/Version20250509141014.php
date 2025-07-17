<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250509141014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ordinateur CHANGE id id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ordinateur ADD CONSTRAINT FK_8712E8DBBF396750 FOREIGN KEY (id) REFERENCES materiel (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ordinateur DROP FOREIGN KEY FK_8712E8DBBF396750
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ordinateur CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
    }
}

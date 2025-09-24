<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250924214757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE builds (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              version_id BIGINT UNSIGNED NOT NULL,
              tag VARCHAR(255) NOT NULL,
              revision SMALLINT NOT NULL,
              context CLOB DEFAULT NULL --(DC2Type:json)
              ,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              CONSTRAINT FK_AB264A54BBC2705 FOREIGN KEY (version_id) REFERENCES versions (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_AB264A54BBC2705 ON builds (version_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE projects (
              uuid BLOB NOT NULL --(DC2Type:uuid)
              ,
              name VARCHAR(255) NOT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              PRIMARY KEY(uuid)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE versions (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              project_id BLOB NOT NULL --(DC2Type:uuid)
              ,
              version VARCHAR(255) NOT NULL,
              context CLOB DEFAULT NULL --(DC2Type:json)
              ,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              CONSTRAINT FK_19DC40D2166D1F9C FOREIGN KEY (project_id) REFERENCES projects (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_19DC40D2166D1F9C ON versions (project_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE builds');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE versions');
    }
}

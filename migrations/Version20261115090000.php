<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261115090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraints on versions (project_id, version) and builds (version_id, tag, revision)';
    }

    public function up(Schema $schema): void
    {
        $duplicateVersions = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM (SELECT 1 FROM versions GROUP BY project_id, version HAVING COUNT(*) > 1) d'
        );
        $this->abortIf(
            $duplicateVersions > 0,
            sprintf('%d duplicated (project_id, version) pairs exist in "versions"; clean them up before migrating.', $duplicateVersions)
        );

        $duplicateBuilds = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM (SELECT 1 FROM builds GROUP BY version_id, tag, revision HAVING COUNT(*) > 1) d'
        );
        $this->abortIf(
            $duplicateBuilds > 0,
            sprintf('%d duplicated (version_id, tag, revision) rows exist in "builds"; clean them up before migrating.', $duplicateBuilds)
        );

        $this->addSql('CREATE UNIQUE INDEX uniq_versions_project_version ON versions (project_id, version)');
        $this->addSql('CREATE UNIQUE INDEX uniq_builds_version_tag_revision ON builds (version_id, tag, revision)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_versions_project_version');
        $this->addSql('DROP INDEX uniq_builds_version_tag_revision');
    }
}

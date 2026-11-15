<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * Takes a write lock on the project row until the current transaction ends,
     * so concurrent version/build creation for the same project is serialized.
     *
     * A no-op UPDATE is used instead of SELECT ... FOR UPDATE because SQLite ignores
     * FOR UPDATE; there the first write grabs the database RESERVED lock and other
     * writers wait (busy timeout). On MySQL/PostgreSQL it acts as a row lock.
     */
    public function lockForUpdate(Project $project): void
    {
        $connection = $this->getEntityManager()->getConnection();

        if (!$connection->isTransactionActive()) {
            throw new \LogicException('lockForUpdate() must be called inside a transaction.');
        }

        $connection->executeStatement(
            'UPDATE projects SET updated_at = updated_at WHERE uuid = ?',
            [$project->getUuid()],
        );
    }
}

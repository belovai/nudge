<?php

namespace App\Repository;

use App\Entity\Build;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Build>
 */
class BuildRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Build::class);
    }

    public function getLatestBuildForTag(int $versionId, string $tag): ?Build
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.version = :versionId')
            ->andWhere('b.tag = :tag')
            ->setParameter('versionId', $versionId)
            ->setParameter('tag', $tag)
            ->orderBy('b.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

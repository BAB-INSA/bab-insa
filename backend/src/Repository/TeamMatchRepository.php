<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TeamMatch;
use App\Enum\MatchStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamMatch>
 */
class TeamMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMatch::class);
    }

    /** @return TeamMatch[] */
    public function findPaginated(int $page, int $limit): array
    {
        /** @var TeamMatch[] $result */
        $result = $this->baseQuery()
            ->orderBy('m.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    public function countAll(): int
    {
        return (int) $this->baseQuery()
            ->select('COUNT(m.id)')
            ->getQuery()->getSingleScalarResult();
    }

    /** @return TeamMatch[] */
    public function findRecent(int $limit): array
    {
        /** @var TeamMatch[] $result */
        $result = $this->baseQuery()
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    /** @return TeamMatch[] */
    public function findPendingOlderThan(\DateTimeImmutable $threshold): array
    {
        /** @var TeamMatch[] $result */
        $result = $this->baseQuery()
            ->andWhere('m.status = :status')
            ->andWhere('m.createdAt < :threshold')
            ->setParameter('status', MatchStatus::Pending)
            ->setParameter('threshold', $threshold)
            ->getQuery()->getResult();

        return $result;
    }

    private function baseQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('m')
            ->where('m.deletedAt IS NULL');
    }
}

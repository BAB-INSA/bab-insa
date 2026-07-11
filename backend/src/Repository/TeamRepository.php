<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /** @return Team[] */
    public function findPaginated(int $page, int $limit): array
    {
        /** @var Team[] $result */
        $result = $this->createQueryBuilder('t')
            ->where('t.deletedAt IS NULL')
            ->orderBy('t.eloRating', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.deletedAt IS NULL')
            ->getQuery()->getSingleScalarResult();
    }

    /** @return Team[] */
    public function findByPlayer(int $playerId): array
    {
        /** @var Team[] $result */
        $result = $this->createQueryBuilder('t')
            ->where('t.deletedAt IS NULL')
            ->andWhere('t.player1 = :pid OR t.player2 = :pid')
            ->setParameter('pid', $playerId)
            ->orderBy('t.eloRating', 'DESC')
            ->getQuery()->getResult();

        return $result;
    }
}

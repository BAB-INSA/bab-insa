<?php

namespace App\Repository;

use App\Entity\SoloMatch;
use App\Enum\MatchStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SoloMatch>
 */
class SoloMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SoloMatch::class);
    }

    /**
     * @return SoloMatch[]
     */
    public function findFiltered(
        ?int $playerId,
        ?MatchStatus $status,
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
        int $page,
        int $limit,
    ): array {
        $qb = $this->baseQuery();
        $this->applyFilters($qb, $playerId, $status, $from, $to);

        /** @var SoloMatch[] $result */
        $result = $qb->orderBy('m.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    /** @return SoloMatch[] */
    public function findRecent(int $limit): array
    {
        /** @var SoloMatch[] $result */
        $result = $this->baseQuery()
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    /** @return SoloMatch[] */
    public function findByPlayer(int $playerId, int $page, int $limit): array
    {
        /** @var SoloMatch[] $result */
        $result = $this->baseQuery()
            ->andWhere('m.player1 = :pid OR m.player2 = :pid')
            ->setParameter('pid', $playerId)
            ->orderBy('m.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    /** @return SoloMatch[] */
    public function findPendingOlderThan(\DateTimeImmutable $threshold): array
    {
        /** @var SoloMatch[] $result */
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

    private function applyFilters(
        QueryBuilder $qb,
        ?int $playerId,
        ?MatchStatus $status,
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
    ): void {
        if ($playerId !== null) {
            $qb->andWhere('m.player1 = :pid OR m.player2 = :pid')
               ->setParameter('pid', $playerId);
        }
        if ($status !== null) {
            $qb->andWhere('m.status = :status')->setParameter('status', $status);
        }
        if ($from !== null) {
            $qb->andWhere('m.createdAt >= :from')->setParameter('from', $from);
        }
        if ($to !== null) {
            $qb->andWhere('m.createdAt <= :to')->setParameter('to', $to);
        }
    }
}

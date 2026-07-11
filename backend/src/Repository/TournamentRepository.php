<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tournament;
use App\Enum\TournamentStatus;
use App\Enum\TournamentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tournament>
 */
class TournamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

    /** @return Tournament[] */
    public function findFiltered(
        ?TournamentStatus $status,
        ?TournamentType $type,
        int $page,
        int $limit,
    ): array {
        $qb = $this->createQueryBuilder('t')
            ->where('t.deletedAt IS NULL');

        if ($status !== null) {
            $qb->andWhere('t.status = :status')->setParameter('status', $status);
        }
        if ($type !== null) {
            $qb->andWhere('t.type = :type')->setParameter('type', $type);
        }

        /** @var Tournament[] $result */
        $result = $qb->orderBy('t.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    public function countFiltered(?TournamentStatus $status, ?TournamentType $type): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.deletedAt IS NULL');

        if ($status !== null) {
            $qb->andWhere('t.status = :status')->setParameter('status', $status);
        }
        if ($type !== null) {
            $qb->andWhere('t.type = :type')->setParameter('type', $type);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

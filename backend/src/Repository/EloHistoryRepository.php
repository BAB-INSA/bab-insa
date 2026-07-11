<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EloHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EloHistory>
 */
class EloHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EloHistory::class);
    }

    /** @return EloHistory[] */
    public function findRecent(int $limit): array
    {
        /** @var EloHistory[] $result */
        $result = $this->createQueryBuilder('e')
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    /** @return EloHistory[] */
    public function findByPlayer(int $playerId, int $page, int $limit): array
    {
        /** @var EloHistory[] $result */
        $result = $this->createQueryBuilder('e')
            ->where('e.player = :pid')
            ->setParameter('pid', $playerId)
            ->orderBy('e.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }
}

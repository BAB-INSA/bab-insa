<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /** @return Player[] */
    public function findPaginated(int $page, int $limit): array
    {
        /** @var Player[] $result */
        $result = $this->baseQuery()
            ->orderBy('p.eloRating', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    /** @return Player[] */
    public function findTopBySoloElo(int $limit): array
    {
        /** @var Player[] $result */
        $result = $this->baseQuery()
            ->orderBy('p.eloRating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    /** @return Player[] */
    public function findTopByTeamElo(int $limit): array
    {
        /** @var Player[] $result */
        $result = $this->baseQuery()
            ->orderBy('p.teamEloRating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $result;
    }

    public function updateRanks(): void
    {
        // Remet à jour le rang de chaque joueur par ordre d'elo
        /** @var Player[] $players */
        $players = $this->baseQuery()
            ->orderBy('p.eloRating', 'DESC')
            ->getQuery()->getResult();

        foreach ($players as $rank => $player) {
            $player->setRank($rank + 1);
        }
    }

    public function updateTeamRanks(): void
    {
        /** @var Player[] $players */
        $players = $this->baseQuery()
            ->orderBy('p.teamEloRating', 'DESC')
            ->getQuery()->getResult();

        foreach ($players as $rank => $player) {
            $player->setTeamRank($rank + 1);
        }
    }

    private function baseQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->where('p.deletedAt IS NULL');
    }
}

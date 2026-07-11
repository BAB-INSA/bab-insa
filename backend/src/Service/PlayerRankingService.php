<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;

class PlayerRankingService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PlayerRepository $playerRepository,
    ) {
    }

    public function updateSoloRanks(): void
    {
        $this->playerRepository->updateRanks();
        $this->em->flush();
    }

    public function updateTeamRanks(): void
    {
        $this->playerRepository->updateTeamRanks();
        $this->em->flush();
    }
}

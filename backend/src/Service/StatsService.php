<?php

namespace App\Service;

use App\Dto\Output\StatsOutput;
use Doctrine\ORM\EntityManagerInterface;

class StatsService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function getStats(): StatsOutput
    {
        $conn = $this->em->getConnection();

        /** @var scalar $playersCount */
        $playersCount = $conn->fetchOne('SELECT COUNT(*) FROM players WHERE deleted_at IS NULL');
        /** @var scalar $matchesCount */
        $matchesCount = $conn->fetchOne('SELECT COUNT(*) FROM matches WHERE deleted_at IS NULL');
        /** @var scalar $teamsCount */
        $teamsCount = $conn->fetchOne('SELECT COUNT(*) FROM teams WHERE deleted_at IS NULL');
        /** @var scalar $teamMatchesCount */
        $teamMatchesCount = $conn->fetchOne('SELECT COUNT(*) FROM team_matches WHERE deleted_at IS NULL');
        /** @var scalar $tournamentsCount */
        $tournamentsCount = $conn->fetchOne('SELECT COUNT(*) FROM tournaments WHERE deleted_at IS NULL');

        $counts = [
            'players'      => (int) $playersCount,
            'matches'      => (int) $matchesCount,
            'teams'        => (int) $teamsCount,
            'team_matches' => (int) $teamMatchesCount,
            'tournaments'  => (int) $tournamentsCount,
        ];

        return $this->mapper->countsToStats($counts);
    }
}

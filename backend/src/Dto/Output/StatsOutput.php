<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class StatsOutput
{
    public int $totalPlayers;
    public int $totalMatches;
    public int $totalTeams;
    public int $totalTeamMatches;
    public int $totalTournaments;
}

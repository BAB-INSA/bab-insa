<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class PlayerOutput
{
    public int $id;
    public string $username;
    public string $slug;

    // Solo
    public float $eloRating;
    public int $rank;
    public int $totalMatches;
    public int $wins;
    public int $losses;

    // Team
    public float $teamEloRating;
    public int $teamRank;
    public int $teamTotalMatches;
    public int $teamWins;
    public int $teamLosses;

    public string $createdAt;
}

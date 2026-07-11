<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class TeamEloHistoryOutput
{
    public int $id;
    public int $playerId;
    public int $teamMatchId;
    public float $eloBefore;
    public float $eloAfter;
    public float $eloChange;
    public ?int $opponentTeamId = null;
    public string $createdAt;
}

<?php

namespace App\Dto\Output;

final class EloHistoryOutput
{
    public int $id;
    public int $playerId;
    public int $matchId;
    public float $eloBefore;
    public float $eloAfter;
    public float $eloChange;
    public ?int $opponentId;
    public string $createdAt;
}

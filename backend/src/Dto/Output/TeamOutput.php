<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class TeamOutput
{
    public int $id;
    public string $name;
    public string $slug;
    public PlayerMinOutput $player1;
    public PlayerMinOutput $player2;
    public float $eloRating;
    public int $totalMatches;
    public int $wins;
    public int $losses;
    public string $createdAt;
}

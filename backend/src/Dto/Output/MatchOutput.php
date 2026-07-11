<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class MatchOutput
{
    public int $id;
    public PlayerMinOutput $player1;
    public PlayerMinOutput $player2;
    public ?PlayerMinOutput $winner;
    public string $status;
    public ?TournamentMinOutput $tournament;
    public ?string $confirmedAt;
    public string $createdAt;
    public string $updatedAt;
}

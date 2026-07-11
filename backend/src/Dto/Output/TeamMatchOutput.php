<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class TeamMatchOutput
{
    public int $id;
    public TeamMinOutput $team1;
    public TeamMinOutput $team2;
    public ?TeamMinOutput $winnerTeam;
    public string $status;
    public ?TournamentMinOutput $tournament;
    public ?string $confirmedAt;
    public string $createdAt;
    public string $updatedAt;
}

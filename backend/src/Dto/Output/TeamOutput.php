<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class TeamOutput
{
    public int $id;
    public string $name;
    public string $slug;
    // Joueurs complets, comme dans l'API Go (le front affiche leurs stats sur la page équipe)
    public PlayerOutput $player1;
    public PlayerOutput $player2;
    public float $eloRating;
    public int $totalMatches;
    public int $wins;
    public int $losses;
    public string $createdAt;
}

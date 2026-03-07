<?php

namespace App\Dto\Output;

/** Version minimaliste du Player pour les réponses imbriquées (dans Match, Team, etc.) */
final class PlayerMinOutput
{
    public int $id;
    public string $username;
    public string $slug;
    public float $eloRating;
    public int $rank;
}

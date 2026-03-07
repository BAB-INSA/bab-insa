<?php

namespace App\Dto\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateTeamMatchInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $team1Id;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $team2Id;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $winnerTeamId;

    #[Assert\Positive]
    public ?int $tournamentId = null;
}

<?php

namespace App\Dto\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateMatchInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $player1Id;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $player2Id;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $winnerId;

    #[Assert\Positive]
    public ?int $tournamentId = null;
}

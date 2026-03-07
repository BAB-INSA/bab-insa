<?php

namespace App\Dto\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateTeamInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $player1Id;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $player2Id;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $name;
}

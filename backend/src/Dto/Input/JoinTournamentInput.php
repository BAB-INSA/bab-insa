<?php

namespace App\Dto\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class JoinTournamentInput
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $teamId;
}

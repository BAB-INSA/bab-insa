<?php

namespace App\Dto\Input;

use App\Enum\MatchStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateMatchStatusInput
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['confirmed', 'rejected', 'cancelled'])]
    public string $status;

    public function getStatus(): MatchStatus
    {
        return MatchStatus::from($this->status);
    }
}

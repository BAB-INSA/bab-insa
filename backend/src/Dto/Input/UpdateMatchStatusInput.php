<?php

declare(strict_types=1);

namespace App\Dto\Input;

use App\Enum\MatchStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateMatchStatusInput
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['confirmed', 'rejected', 'cancelled'])]
    public string $status;

    public function statusEnum(): MatchStatus
    {
        return MatchStatus::from($this->status);
    }
}

<?php

declare(strict_types=1);

namespace App\Dto\Input;

use App\Enum\TournamentStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateTournamentInput
{
    #[Assert\Length(min: 2, max: 255)]
    public ?string $name = null;

    #[Assert\Choice(choices: ['opened', 'ongoing', 'finished'])]
    public ?string $status = null;

    public ?string $description = null;

    public function statusEnum(): ?TournamentStatus
    {
        return $this->status !== null ? TournamentStatus::from($this->status) : null;
    }
}

<?php

declare(strict_types=1);

namespace App\Dto\Input;

use App\Enum\MatchStatus;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comme dans l'API Go (UpdateMatchStatusRequest) : status et winner sont
 * tous deux optionnels, un admin peut corriger le vainqueur sans changer le statut.
 */
final class UpdateMatchStatusInput
{
    #[Assert\Choice(choices: ['confirmed', 'rejected', 'cancelled'])]
    public ?string $status = null;

    /** Vainqueur (match solo) — doit être l'un des deux joueurs. */
    #[Assert\Positive]
    public ?int $winnerId = null;

    /** Équipe gagnante (match équipe) — doit être l'une des deux équipes. */
    #[Assert\Positive]
    public ?int $winnerTeamId = null;

    public function statusEnum(): ?MatchStatus
    {
        return $this->status !== null ? MatchStatus::from($this->status) : null;
    }
}

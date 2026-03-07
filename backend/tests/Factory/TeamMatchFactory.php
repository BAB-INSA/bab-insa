<?php

namespace App\Tests\Factory;

use App\Entity\TeamMatch;
use App\Enum\MatchStatus;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<TeamMatch>
 */
final class TeamMatchFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return TeamMatch::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'team1'  => TeamFactory::new(),
            'team2'  => TeamFactory::new(),
            'status' => MatchStatus::Pending,
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }

    // --- Fluent builders ---

    public function pending(): static
    {
        return $this->with(['status' => MatchStatus::Pending]);
    }

    public function confirmed(): static
    {
        return $this->with([
            'status'      => MatchStatus::Confirmed,
            'confirmedAt' => new \DateTimeImmutable(),
        ]);
    }
}

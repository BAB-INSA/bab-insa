<?php

namespace App\Tests\Factory;

use App\Entity\Tournament;
use App\Enum\TournamentStatus;
use App\Enum\TournamentType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Tournament>
 */
final class TournamentFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Tournament::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'name'   => self::faker()->unique()->words(3, true),
            'type'   => TournamentType::Solo,
            'status' => TournamentStatus::Opened,
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }

    // --- Fluent builders ---

    public function solo(): static
    {
        return $this->with(['type' => TournamentType::Solo]);
    }

    public function team(): static
    {
        return $this->with(['type' => TournamentType::Team]);
    }

    public function opened(): static
    {
        return $this->with(['status' => TournamentStatus::Opened]);
    }

    public function ongoing(): static
    {
        return $this->with(['status' => TournamentStatus::Ongoing]);
    }

    public function finished(): static
    {
        return $this->with(['status' => TournamentStatus::Finished]);
    }
}

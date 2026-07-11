<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\SoloMatch;
use App\Enum\MatchStatus;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<SoloMatch>
 */
final class SoloMatchFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return SoloMatch::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'player1' => PlayerFactory::new(),
            'player2' => PlayerFactory::new(),
            'status'  => MatchStatus::Pending,
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

    public function rejected(): static
    {
        return $this->with(['status' => MatchStatus::Rejected]);
    }

    public function cancelled(): static
    {
        return $this->with(['status' => MatchStatus::Cancelled]);
    }

    public function oldPending(): static
    {
        return $this->with(['status' => MatchStatus::Pending])
            ->afterInstantiate(function (SoloMatch $match): void {
                // Simule un match vieux de 72h pour tester l'auto-validation
                $reflection = new \ReflectionProperty(SoloMatch::class, 'createdAt');
                $reflection->setValue($match, new \DateTimeImmutable('-72 hours'));
            });
    }
}

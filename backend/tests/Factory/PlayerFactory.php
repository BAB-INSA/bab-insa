<?php

namespace App\Tests\Factory;

use App\Entity\Player;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Player>
 */
final class PlayerFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Player::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'user'     => UserFactory::new(),
            'username' => self::faker()->unique()->userName(),
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(function (Player $player): void {
            // Le username du Player doit correspondre à celui du User
            $player->setUsername($player->getUser()->getUsername());
        });
    }

    // --- Fluent builders ---

    public function withElo(float $elo): static
    {
        return $this->with(['eloRating' => $elo]);
    }

    public function withTeamElo(float $elo): static
    {
        return $this->with(['teamEloRating' => $elo]);
    }
}

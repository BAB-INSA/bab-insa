<?php

namespace App\Tests\Factory;

use App\Entity\Team;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Team>
 */
final class TeamFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Team::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'player1' => PlayerFactory::new(),
            'player2' => PlayerFactory::new(),
            'name'    => self::faker()->unique()->words(2, true),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}

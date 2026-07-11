<?php

declare(strict_types=1);

namespace App\Story;

use App\Tests\Factory\PlayerFactory;
use App\Tests\Factory\SoloMatchFactory;
use App\Tests\Factory\TeamFactory;
use App\Tests\Factory\TeamMatchFactory;
use App\Tests\Factory\TournamentFactory;
use App\Tests\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        // ── Comptes avec credentials connus ──────────────────────────────────

        $superAdmin = UserFactory::new()->asSuperAdmin()->withCredentials('superadmin@bab-insa.fr', 'superadmin')->create();
        $admin      = UserFactory::new()->asAdmin()->withCredentials('admin@bab-insa.fr', 'admin')->create();
        $testUser   = UserFactory::new()->withCredentials('user@bab-insa.fr', 'user')->create();

        $superAdminPlayer = PlayerFactory::new()->create(['user' => $superAdmin, 'username' => $superAdmin->getUsername()]);
        $adminPlayer      = PlayerFactory::new()->create(['user' => $admin,      'username' => $admin->getUsername()]);
        $testPlayer       = PlayerFactory::new()->create(['user' => $testUser,   'username' => $testUser->getUsername()]);

        // ── 20 joueurs aléatoires ─────────────────────────────────────────────

        $randomPlayers = PlayerFactory::createMany(20);
        $allPlayers    = array_merge([$superAdminPlayer, $adminPlayer, $testPlayer], $randomPlayers);

        // ── 30 matchs solo confirmés ──────────────────────────────────────────

        for ($i = 0; $i < 30; $i++) {
            $pair = $this->pickTwo($allPlayers);
            SoloMatchFactory::new()->confirmed()->create([
                'player1' => $pair[0],
                'player2' => $pair[1],
                'winner'  => $pair[rand(0, 1)],
            ]);
        }

        // ── 10 matchs solo en attente ─────────────────────────────────────────

        for ($i = 0; $i < 10; $i++) {
            $pair = $this->pickTwo($allPlayers);
            SoloMatchFactory::new()->pending()->create([
                'player1' => $pair[0],
                'player2' => $pair[1],
            ]);
        }

        // ── 5 équipes (paires de joueurs disjointes) ──────────────────────────

        $pool = $allPlayers;
        shuffle($pool);
        // On prend 10 joueurs distincts (5 paires) depuis le pool
        $teamPool = array_slice($pool, 0, 10);

        $teams = [];
        foreach (array_chunk($teamPool, 2) as $pair) {
            $teams[] = TeamFactory::new()->create(['player1' => $pair[0], 'player2' => $pair[1]]);
        }

        // ── 15 matchs en équipe confirmés ────────────────────────────────────

        if (count($teams) >= 2) {
            for ($i = 0; $i < 15; $i++) {
                $pair = $this->pickTwo($teams);
                TeamMatchFactory::new()->confirmed()->create([
                    'team1'      => $pair[0],
                    'team2'      => $pair[1],
                    'winnerTeam' => $pair[rand(0, 1)],
                ]);
            }

            for ($i = 0; $i < 5; $i++) {
                $pair = $this->pickTwo($teams);
                TeamMatchFactory::new()->pending()->create([
                    'team1' => $pair[0],
                    'team2' => $pair[1],
                ]);
            }
        }

        // ── Tournois ──────────────────────────────────────────────────────────

        TournamentFactory::new()->solo()->opened()->create(['name'   => 'Tournoi Solo Printemps 2026']);
        TournamentFactory::new()->solo()->ongoing()->create(['name'  => 'Tournoi Solo Hiver 2025']);
        TournamentFactory::new()->team()->opened()->create(['name'   => 'Tournoi Équipe Printemps 2026']);
        TournamentFactory::new()->team()->finished()->create(['name' => 'Tournoi Équipe Automne 2025']);
    }

    /**
     * Retourne 2 éléments distincts aléatoires du tableau.
     *
     * @template T
     * @param T[] $items
     * @return array{T, T}
     */
    private function pickTwo(array $items): array
    {
        $keys = array_rand($items, 2);
        return [$items[$keys[0]], $items[$keys[1]]];
    }
}

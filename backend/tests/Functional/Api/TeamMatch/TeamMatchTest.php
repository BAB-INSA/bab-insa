<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\TeamMatch;

use App\Tests\Factory\PlayerFactory;
use App\Tests\Factory\TeamFactory;
use App\Tests\Factory\TeamMatchFactory;
use App\Tests\Functional\Api\AbstractApiTestCase;

/**
 * 7.3 — Tester le flux team match.
 */
class TeamMatchTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // GET /api/team-matches — public
    // -------------------------------------------------------------------------

    public function testGetTeamMatchesIsPublic(): void
    {
        TeamMatchFactory::createMany(3);

        $response = $this->client->request('GET', '/api/team-matches');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);
    }

    // -------------------------------------------------------------------------
    // POST /api/team-matches — requires auth
    // -------------------------------------------------------------------------

    public function testCreateTeamMatchRequiresAuth(): void
    {
        $team = TeamFactory::createOne();

        $this->client->request('POST', '/api/team-matches', [
            'json' => ['team2Id' => $team->getId(), 'winnerTeamId' => $team->getId()],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateTeamMatchSuccess(): void
    {
        ['token' => $token, 'player' => $player] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $opponent = TeamFactory::createOne();
        $partner  = PlayerFactory::createOne();

        // Créer une team dont le player actuel est membre
        $myTeamResponse = $this->client->request('POST', '/api/teams', [
            'json' => [
                'player1Id' => $player->getId(),
                'player2Id' => $partner->getId(),
                'name'      => 'My Team',
            ],
        ]);
        $myTeamId = $myTeamResponse->toArray()['id'];

        $response = $this->client->request('POST', '/api/team-matches', [
            'json' => [
                'team1Id'      => $myTeamId,
                'team2Id'      => $opponent->getId(),
                'winnerTeamId' => $opponent->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
    }

    // -------------------------------------------------------------------------
    // PATCH /api/team-matches/{id}/cancel — admin only
    // -------------------------------------------------------------------------

    public function testCancelTeamMatchRequiresAdmin(): void
    {
        ['token' => $token] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $match = TeamMatchFactory::new()->pending()->create();

        $this->client->request('PATCH', '/api/team-matches/' . $match->getId() . '/cancel');

        $this->assertResponseStatusCodeSame(403);
    }
}

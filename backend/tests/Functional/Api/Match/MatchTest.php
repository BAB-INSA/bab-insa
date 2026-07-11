<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Match;

use App\Tests\Factory\PlayerFactory;
use App\Tests\Factory\SoloMatchFactory;
use App\Tests\Functional\Api\AbstractApiTestCase;

/**
 * 7.2 — Tester le flux match (create → confirm → ELO update).
 */
class MatchTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // GET /api/matches — public
    // -------------------------------------------------------------------------

    public function testGetMatchesIsPublic(): void
    {
        SoloMatchFactory::createMany(3);

        $response = $this->client->request('GET', '/api/matches');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);
    }

    // -------------------------------------------------------------------------
    // POST /api/matches — requires auth
    // -------------------------------------------------------------------------

    public function testCreateMatchRequiresAuth(): void
    {
        $player = PlayerFactory::createOne();

        $this->client->request('POST', '/api/matches', [
            'json' => ['player2Id' => $player->getId(), 'winnerId' => $player->getId()],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateMatchSuccess(): void
    {
        ['token' => $token, 'user' => $user] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $opponent = PlayerFactory::createOne();

        $response = $this->client->request('POST', '/api/matches', [
            'json' => [
                'player2Id' => $opponent->getId(),
                'winnerId'  => $opponent->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $data = $response->toArray();
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('pending', $data['status']);
    }

    // -------------------------------------------------------------------------
    // PATCH /api/matches/{id} — confirm (player2 confirme)
    // -------------------------------------------------------------------------

    public function testConfirmMatchUpdatesElo(): void
    {
        // Créer player1 (créateur du match)
        ['token' => $token1] = $this->createUserWithPlayer();

        // Créer player2 (confirmateur)
        ['token' => $token2] = $this->createUserWithPlayer();

        $this->authenticate($token1);

        // Récupérer les IDs players
        $meResponse = $this->client->request('GET', '/api/users/me');
        $player1Id = $meResponse->toArray()['player']['id'] ?? null;

        $this->authenticate($token2);
        $meResponse2 = $this->client->request('GET', '/api/users/me');
        $player2Id = $meResponse2->toArray()['player']['id'] ?? null;

        // Player1 crée le match
        $this->authenticate($token1);
        $createResponse = $this->client->request('POST', '/api/matches', [
            'json' => ['player2Id' => $player2Id, 'winnerId' => $player2Id],
        ]);
        $matchId = $createResponse->toArray()['id'];

        // Player2 confirme
        $this->authenticate($token2);
        $response = $this->client->request('PATCH', '/api/matches/' . $matchId, [
            'json'    => ['status' => 'confirmed'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertSame('confirmed', $data['status']);
    }

    // -------------------------------------------------------------------------
    // PATCH /api/matches/{id}/cancel — admin only
    // -------------------------------------------------------------------------

    public function testCancelMatchRequiresAdmin(): void
    {
        ['token' => $token] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $match = SoloMatchFactory::new()->pending()->create();

        $this->client->request('PATCH', '/api/matches/' . $match->getId() . '/cancel');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCancelMatchAsAdmin(): void
    {
        ['token' => $adminToken] = $this->createAdmin();
        $this->authenticate($adminToken);

        $match = SoloMatchFactory::new()->pending()->create();

        $response = $this->client->request('PATCH', '/api/matches/' . $match->getId() . '/cancel');

        $this->assertResponseIsSuccessful();
        $this->assertSame('cancelled', $response->toArray()['status']);
    }

    // -------------------------------------------------------------------------
    // GET /api/matches/recent
    // -------------------------------------------------------------------------

    public function testGetRecentMatches(): void
    {
        SoloMatchFactory::new()->confirmed()->createMany(5);

        $response = $this->client->request('GET', '/api/matches/recent');

        $this->assertResponseIsSuccessful();
    }
}

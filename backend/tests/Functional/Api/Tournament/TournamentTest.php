<?php

namespace App\Tests\Functional\Api\Tournament;

use App\Tests\Factory\TournamentFactory;
use App\Tests\Functional\Api\AbstractApiTestCase;

/**
 * 7.4 — Tester les tournois (join/leave/match).
 */
class TournamentTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // GET /api/tournaments — public
    // -------------------------------------------------------------------------

    public function testGetTournamentsIsPublic(): void
    {
        TournamentFactory::createMany(3);

        $response = $this->client->request('GET', '/api/tournaments');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);
        $this->assertCount(3, $data['member']);
    }

    // -------------------------------------------------------------------------
    // GET /api/tournaments/{id}
    // -------------------------------------------------------------------------

    public function testGetTournamentById(): void
    {
        $tournament = TournamentFactory::new()->solo()->create();

        $response = $this->client->request('GET', '/api/tournaments/'.$tournament->getId());

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertSame($tournament->getName(), $data['name']);
    }

    // -------------------------------------------------------------------------
    // POST /api/tournaments — admin only
    // -------------------------------------------------------------------------

    public function testCreateTournamentRequiresAdmin(): void
    {
        ['token' => $token] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $this->client->request('POST', '/api/tournaments', [
            'json' => ['name' => 'Test Tourney', 'type' => 'solo'],
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateTournamentAsAdmin(): void
    {
        ['token' => $adminToken] = $this->createAdmin();
        $this->authenticate($adminToken);

        $response = $this->client->request('POST', '/api/tournaments', [
            'json' => ['name' => 'Admin Tourney', 'type' => 'solo'],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $data = $response->toArray();
        $this->assertSame('Admin Tourney', $data['name']);
        $this->assertSame('opened', $data['status']);
    }

    // -------------------------------------------------------------------------
    // POST /api/tournaments/{id}/join — requires auth
    // -------------------------------------------------------------------------

    public function testJoinTournamentRequiresAuth(): void
    {
        $tournament = TournamentFactory::new()->solo()->create();

        $this->client->request('POST', '/api/tournaments/'.$tournament->getId().'/join', [
            'json' => [],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testJoinSoloTournament(): void
    {
        ['token' => $token] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $tournament = TournamentFactory::new()->solo()->opened()->create();

        $response = $this->client->request('POST', '/api/tournaments/'.$tournament->getId().'/join', [
            'json' => [],
        ]);

        $this->assertResponseIsSuccessful();
    }

    // -------------------------------------------------------------------------
    // DELETE /api/tournaments/{id} — admin only
    // -------------------------------------------------------------------------

    public function testDeleteTournamentRequiresAdmin(): void
    {
        ['token' => $token] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $tournament = TournamentFactory::createOne();

        $this->client->request('DELETE', '/api/tournaments/'.$tournament->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    // -------------------------------------------------------------------------
    // GET /api/tournaments/{id}/teams
    // -------------------------------------------------------------------------

    public function testGetTournamentTeams(): void
    {
        $tournament = TournamentFactory::createOne();

        $response = $this->client->request('GET', '/api/tournaments/'.$tournament->getId().'/teams');

        $this->assertResponseIsSuccessful();
    }

    // -------------------------------------------------------------------------
    // GET /api/tournaments/{id}/matches
    // -------------------------------------------------------------------------

    public function testGetTournamentMatches(): void
    {
        $tournament = TournamentFactory::createOne();

        $response = $this->client->request('GET', '/api/tournaments/'.$tournament->getId().'/matches');

        $this->assertResponseIsSuccessful();
    }
}

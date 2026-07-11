<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Team;

use App\Tests\Factory\PlayerFactory;
use App\Tests\Factory\TeamFactory;
use App\Tests\Functional\Api\AbstractApiTestCase;

class TeamTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // GET /api/teams — public
    // -------------------------------------------------------------------------

    public function testGetTeamsIsPublic(): void
    {
        TeamFactory::createMany(3);

        $response = $this->client->request('GET', '/api/teams');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);
        $this->assertCount(3, $data['member']);
    }

    // -------------------------------------------------------------------------
    // GET /api/teams/{id}
    // -------------------------------------------------------------------------

    public function testGetTeamById(): void
    {
        $team = TeamFactory::createOne();

        $response = $this->client->request('GET', '/api/teams/' . $team->getId());

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertSame($team->getName(), $data['name']);
    }

    // -------------------------------------------------------------------------
    // POST /api/teams — requires auth
    // -------------------------------------------------------------------------

    public function testCreateTeamRequiresAuth(): void
    {
        $partner = PlayerFactory::createOne();

        $this->client->request('POST', '/api/teams', [
            'json' => ['player2Id' => $partner->getId(), 'name' => 'Test Team'],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateTeamSuccess(): void
    {
        ['token' => $token] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $partner = PlayerFactory::createOne();

        $response = $this->client->request('POST', '/api/teams', [
            'json' => ['player2Id' => $partner->getId(), 'name' => 'Dream Team'],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $data = $response->toArray();
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Dream Team', $data['name']);
        $this->assertNotEmpty($data['slug']);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/teams/{id} — admin only
    // -------------------------------------------------------------------------

    public function testDeleteTeamRequiresAdmin(): void
    {
        ['token' => $token] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $team = TeamFactory::createOne();

        $this->client->request('DELETE', '/api/teams/' . $team->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteTeamAsAdmin(): void
    {
        ['token' => $adminToken] = $this->createAdmin();
        $this->authenticate($adminToken);

        $team = TeamFactory::createOne();

        $this->client->request('DELETE', '/api/teams/' . $team->getId());

        $this->assertResponseStatusCodeSame(204);
    }
}

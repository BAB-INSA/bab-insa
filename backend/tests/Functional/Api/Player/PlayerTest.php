<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Player;

use App\Tests\Factory\PlayerFactory;
use App\Tests\Functional\Api\AbstractApiTestCase;

/**
 * 7.2 (partiel) — Tester les endpoints /players.
 */
class PlayerTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // GET /api/players
    // -------------------------------------------------------------------------

    public function testGetPlayersIsPublic(): void
    {
        PlayerFactory::createMany(3);

        $response = $this->client->request('GET', '/api/players');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);
        $this->assertCount(3, $data['member']);
    }

    public function testGetPlayersIsPaginated(): void
    {
        PlayerFactory::createMany(25);

        $response = $this->client->request('GET', '/api/players');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        // La pagination par défaut est 20/page
        $this->assertCount(20, $data['member']);
        $this->assertSame(25, $data['totalItems']);
    }

    public function testGetPlayersPaginationClientSide(): void
    {
        PlayerFactory::createMany(5);

        $response = $this->client->request('GET', '/api/players?itemsPerPage=2&page=1');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertCount(2, $data['member']);
    }

    // -------------------------------------------------------------------------
    // GET /api/players/{id}
    // -------------------------------------------------------------------------

    public function testGetPlayerById(): void
    {
        $player = PlayerFactory::createOne();

        $response = $this->client->request('GET', '/api/players/' . $player->getId());

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertSame($player->getUsername(), $data['username']);
    }

    public function testGetPlayerNotFound(): void
    {
        $this->client->request('GET', '/api/players/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // GET /api/players/top
    // -------------------------------------------------------------------------

    public function testGetTopPlayers(): void
    {
        PlayerFactory::new()->withElo(1500)->create();
        PlayerFactory::new()->withElo(1300)->create();
        PlayerFactory::new()->withElo(1100)->create();

        $response = $this->client->request('GET', '/api/players/top');

        $this->assertResponseIsSuccessful();
    }

    // -------------------------------------------------------------------------
    // GET /api/players/{id}/elo-history
    // -------------------------------------------------------------------------

    public function testGetPlayerEloHistory(): void
    {
        $player = PlayerFactory::createOne();

        $response = $this->client->request('GET', '/api/players/' . $player->getId() . '/elo-history');

        $this->assertResponseIsSuccessful();
    }

    // -------------------------------------------------------------------------
    // GET /api/players/{id}/matches
    // -------------------------------------------------------------------------

    public function testGetPlayerMatches(): void
    {
        $player = PlayerFactory::createOne();

        $response = $this->client->request('GET', '/api/players/' . $player->getId() . '/matches');

        $this->assertResponseIsSuccessful();
    }

    // -------------------------------------------------------------------------
    // GET /api/players/{id}/teams — public (comme l'API Go)
    // -------------------------------------------------------------------------

    public function testGetPlayerTeamsIsPublic(): void
    {
        $player = PlayerFactory::createOne();

        $this->client->request('GET', '/api/players/' . $player->getId() . '/teams');

        $this->assertResponseIsSuccessful();
    }

    public function testGetPlayerTeamsWithAuth(): void
    {
        ['token' => $token] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $player = PlayerFactory::createOne();

        $response = $this->client->request('GET', '/api/players/' . $player->getId() . '/teams');

        $this->assertResponseIsSuccessful();
    }
}

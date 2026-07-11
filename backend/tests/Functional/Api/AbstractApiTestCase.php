<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Player;
use App\Entity\User;
use App\Tests\Factory\PlayerFactory;
use App\Tests\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractApiTestCase extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    /**
     * Crée un user + player en base et retourne le JWT.
     *
     * @param array<string, mixed> $userOverrides
     * @return array{user: User, player: Player, token: string}
     */
    protected function createUserWithPlayer(array $userOverrides = []): array
    {
        $user = UserFactory::new()->with($userOverrides)->create();
        $player = PlayerFactory::new()->with(['user' => $user, 'username' => $user->getUsername()])->create();

        return ['user' => $user, 'player' => $player, 'token' => $this->getToken($user)];
    }

    /**
     * Crée un admin + player en base et retourne le JWT.
     *
     * @return array{user: User, player: Player, token: string}
     */
    protected function createAdmin(): array
    {
        $user = UserFactory::new()->asAdmin()->create();
        $player = PlayerFactory::new()->with(['user' => $user, 'username' => $user->getUsername()])->create();

        return ['user' => $user, 'player' => $player, 'token' => $this->getToken($user)];
    }

    /**
     * Authentifie le client HTTP avec le token JWT donné.
     */
    protected function authenticate(string $token): void
    {
        $this->client->setDefaultOptions([
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
    }

    /**
     * Obtient un JWT via POST /auth/login.
     */
    private function getToken(User $user): string
    {
        $response = $this->client->request('POST', '/auth/login', [
            'json' => ['email' => $user->getEmail(), 'password' => 'password'],
        ]);

        return $response->toArray()['token'];
    }
}

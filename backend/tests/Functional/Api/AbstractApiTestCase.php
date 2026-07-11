<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
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
     * @return array{user: User, token: string}
     */
    protected function createUserWithPlayer(array $userOverrides = []): array
    {
        /** @var \Zenstruck\Foundry\Persistence\Proxy<User>&User $user */
        $user = UserFactory::new()->with($userOverrides)->create();
        $realUser = $user->_real();
        PlayerFactory::new()->with(['user' => $user, 'username' => $realUser->getUsername()])->create();

        return ['user' => $realUser, 'token' => $this->getToken($realUser)];
    }

    /**
     * Crée un admin + player en base et retourne le JWT.
     *
     * @return array{user: User, token: string}
     */
    protected function createAdmin(): array
    {
        /** @var \Zenstruck\Foundry\Persistence\Proxy<User>&User $user */
        $user = UserFactory::new()->asAdmin()->create();
        $realUser = $user->_real();
        PlayerFactory::new()->with(['user' => $user, 'username' => $realUser->getUsername()])->create();

        return ['user' => $realUser, 'token' => $this->getToken($realUser)];
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

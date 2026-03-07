<?php

namespace App\Tests\Functional\Api\Auth;

use App\Tests\Factory\UserFactory;
use App\Tests\Functional\Api\AbstractApiTestCase;

/**
 * 7.1 — Tester tous les endpoints auth.
 */
class AuthTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // POST /auth/register
    // -------------------------------------------------------------------------

    public function testRegisterSuccess(): void
    {
        $response = $this->client->request('POST', '/auth/register', [
            'json' => [
                'email'    => 'newuser@test.com',
                'username' => 'newuser',
                'password' => 'password123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertSame('newuser@test.com', $data['user']['email']);
    }

    public function testRegisterEmailAlreadyUsed(): void
    {
        UserFactory::new()->withCredentials('taken@test.com', 'someuser')->create();

        $this->client->request('POST', '/auth/register', [
            'json' => [
                'email'    => 'taken@test.com',
                'username' => 'otheruser',
                'password' => 'password123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testRegisterUsernameAlreadyUsed(): void
    {
        UserFactory::new()->withCredentials('other@test.com', 'takenuser')->create();

        $this->client->request('POST', '/auth/register', [
            'json' => [
                'email'    => 'new@test.com',
                'username' => 'takenuser',
                'password' => 'password123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testRegisterValidationError(): void
    {
        $this->client->request('POST', '/auth/register', [
            'json' => ['email' => 'not-an-email', 'username' => 'u', 'password' => ''],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    // -------------------------------------------------------------------------
    // POST /auth/login
    // -------------------------------------------------------------------------

    public function testLoginSuccess(): void
    {
        UserFactory::new()->withCredentials('user@test.com', 'testuser')->create();

        $response = $this->client->request('POST', '/auth/login', [
            'json' => ['email' => 'user@test.com', 'password' => 'password'],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginInvalidPassword(): void
    {
        UserFactory::new()->withCredentials('user@test.com', 'testuser')->create();

        $this->client->request('POST', '/auth/login', [
            'json' => ['email' => 'user@test.com', 'password' => 'wrongpassword'],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginUnknownEmail(): void
    {
        $this->client->request('POST', '/auth/login', [
            'json' => ['email' => 'unknown@test.com', 'password' => 'password'],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginDisabledUser(): void
    {
        UserFactory::new()->withCredentials('disabled@test.com', 'disableduser')->disabled()->create();

        $this->client->request('POST', '/auth/login', [
            'json' => ['email' => 'disabled@test.com', 'password' => 'password'],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    // -------------------------------------------------------------------------
    // POST /auth/refresh
    // -------------------------------------------------------------------------

    public function testRefreshToken(): void
    {
        UserFactory::new()->withCredentials('user@test.com', 'testuser')->create();

        $loginResponse = $this->client->request('POST', '/auth/login', [
            'json' => ['email' => 'user@test.com', 'password' => 'password'],
        ]);
        $refreshToken = $loginResponse->toArray()['refresh_token'];

        $response = $this->client->request('POST', '/auth/refresh', [
            'json' => ['refresh_token' => $refreshToken],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $response->toArray());
    }

    // -------------------------------------------------------------------------
    // POST /auth/logout
    // -------------------------------------------------------------------------

    public function testLogout(): void
    {
        ['token' => $token] = $this->createUserWithPlayer();
        $this->authenticate($token);

        $this->client->request('POST', '/auth/logout');

        $this->assertResponseIsSuccessful();
    }
}

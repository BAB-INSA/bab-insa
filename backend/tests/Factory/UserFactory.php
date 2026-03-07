<?php

namespace App\Tests\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'email'    => self::faker()->unique()->safeEmail(),
            'username' => self::faker()->unique()->userName(),
            'password' => 'password',
            'roles'    => ['ROLE_USER'],
            'enabled'  => true,
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(function (User $user): void {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $user->getPassword())
            );
        });
    }

    // --- Fluent builders ---

    public function asAdmin(): static
    {
        return $this->with(['roles' => ['ROLE_USER', 'ROLE_ADMIN']]);
    }

    public function asSuperAdmin(): static
    {
        return $this->with(['roles' => ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN']]);
    }

    public function disabled(): static
    {
        return $this->with(['enabled' => false]);
    }

    public function withCredentials(string $email, string $username, string $password = 'password'): static
    {
        return $this->with(['email' => $email, 'username' => $username, 'password' => $password]);
    }
}

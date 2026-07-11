<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class UserOutput
{
    public int $id;
    public string $email;
    public string $username;
    public string $slug;
    /** @var string[] */
    public array $roles;
    public bool $enabled;
    public ?string $lastLogin;
    public int $nbConnexion;
    public string $createdAt;
}

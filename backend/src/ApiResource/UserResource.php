<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use App\Dto\Input\AdminUpdateUserInput;
use App\Dto\Input\UpdateUserInput;
use App\State\User\AdminUpdateUserProcessor;
use App\State\User\MeProvider;
use App\State\User\UpdateUserProcessor;
use App\State\User\UserCollectionProvider;
use App\State\User\UserItemProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/users',
            security: 'is_granted(\'ROLE_USER\')',
            provider: UserCollectionProvider::class,
        ),
        new Get(
            uriTemplate: '/users/me',
            security: 'is_granted(\'ROLE_USER\')',
            provider: MeProvider::class,
        ),
        new Get(
            uriTemplate: '/users/{id}',
            security: 'is_granted(\'ROLE_USER\')',
            provider: UserItemProvider::class,
        ),
        new Put(
            uriTemplate: '/users/{id}',
            security: 'is_granted(\'ROLE_USER\')',
            input: UpdateUserInput::class,
            provider: UserItemProvider::class,
            processor: UpdateUserProcessor::class,
        ),
        new Patch(
            uriTemplate: '/users/{id}',
            security: 'is_granted(\'ROLE_ADMIN\')',
            input: AdminUpdateUserInput::class,
            provider: UserItemProvider::class,
            processor: AdminUpdateUserProcessor::class,
        ),
    ],
)]
class UserResource
{
}

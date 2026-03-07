<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Input\CreateMatchInput;
use App\Dto\Input\UpdateMatchStatusInput;
use App\State\Match\CancelMatchProcessor;
use App\State\Match\CreateMatchProcessor;
use App\State\Match\MatchCollectionProvider;
use App\State\Match\MatchItemProvider;
use App\State\Match\RecentMatchesProvider;
use App\State\Match\RejectMatchProcessor;
use App\State\Match\UpdateMatchStatusProcessor;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/matches', provider: MatchCollectionProvider::class),
        new GetCollection(uriTemplate: '/matches/recent', provider: RecentMatchesProvider::class),
        new Get(uriTemplate: '/matches/{id}', provider: MatchItemProvider::class),
        new Post(
            uriTemplate: '/matches',
            security: 'is_granted(\'ROLE_USER\')',
            input: CreateMatchInput::class,
            processor: CreateMatchProcessor::class,
        ),
        new Patch(
            uriTemplate: '/matches/{id}',
            security: 'is_granted(\'ROLE_USER\')',
            input: UpdateMatchStatusInput::class,
            provider: MatchItemProvider::class,
            processor: UpdateMatchStatusProcessor::class,
        ),
        new Patch(
            uriTemplate: '/matches/{id}/reject',
            security: 'is_granted(\'ROLE_USER\')',
            provider: MatchItemProvider::class,
            processor: RejectMatchProcessor::class,
        ),
        new Patch(
            uriTemplate: '/matches/{id}/cancel',
            security: 'is_granted(\'ROLE_ADMIN\')',
            provider: MatchItemProvider::class,
            processor: CancelMatchProcessor::class,
        ),
        new Delete(
            uriTemplate: '/matches/{id}',
            security: 'is_granted(\'ROLE_ADMIN\')',
            provider: MatchItemProvider::class,
        ),
    ],
)]
class MatchResource
{
}

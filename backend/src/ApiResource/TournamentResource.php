<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\Input\CreateTournamentInput;
use App\Dto\Input\JoinTournamentInput;
use App\Dto\Input\UpdateTournamentInput;
use App\State\Tournament\CreateTournamentProcessor;
use App\State\Tournament\JoinTournamentProcessor;
use App\State\Tournament\LeaveTournamentProcessor;
use App\State\Tournament\TournamentCollectionProvider;
use App\State\Tournament\TournamentItemProvider;
use App\State\Tournament\TournamentMatchesProvider;
use App\State\Tournament\TournamentTeamsProvider;
use App\State\Tournament\UpdateTournamentProcessor;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/tournaments', provider: TournamentCollectionProvider::class),
        new Get(uriTemplate: '/tournaments/{id}', provider: TournamentItemProvider::class),
        new GetCollection(uriTemplate: '/tournaments/{id}/teams', provider: TournamentTeamsProvider::class),
        new GetCollection(uriTemplate: '/tournaments/{id}/matches', provider: TournamentMatchesProvider::class),
        new Post(
            uriTemplate: '/tournaments',
            security: 'is_granted(\'ROLE_ADMIN\')',
            input: CreateTournamentInput::class,
            processor: CreateTournamentProcessor::class,
        ),
        new Put(
            uriTemplate: '/tournaments/{id}',
            security: 'is_granted(\'ROLE_ADMIN\')',
            input: UpdateTournamentInput::class,
            provider: TournamentItemProvider::class,
            processor: UpdateTournamentProcessor::class,
        ),
        new Post(
            uriTemplate: '/tournaments/{id}/join',
            security: 'is_granted(\'ROLE_USER\')',
            input: JoinTournamentInput::class,
            provider: TournamentItemProvider::class,
            processor: JoinTournamentProcessor::class,
        ),
        new Delete(
            uriTemplate: '/tournaments/{id}/teams/{teamId}',
            security: 'is_granted(\'ROLE_USER\')',
            provider: TournamentItemProvider::class,
            processor: LeaveTournamentProcessor::class,
        ),
        new Delete(
            uriTemplate: '/tournaments/{id}',
            security: 'is_granted(\'ROLE_ADMIN\')',
            provider: TournamentItemProvider::class,
        ),
    ],
)]
class TournamentResource
{
}

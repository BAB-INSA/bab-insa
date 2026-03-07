<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\Input\CreateTeamInput;
use App\Dto\Input\UpdateTeamInput;
use App\State\Team\CreateTeamProcessor;
use App\State\Team\PlayerTeamsProvider;
use App\State\Team\TeamCollectionProvider;
use App\State\Team\TeamItemProvider;
use App\State\Team\UpdateTeamProcessor;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/teams', provider: TeamCollectionProvider::class),
        new Get(uriTemplate: '/teams/{id}', provider: TeamItemProvider::class),
        new GetCollection(uriTemplate: '/teams/players/{playerId}', provider: PlayerTeamsProvider::class),
        new Post(
            uriTemplate: '/teams',
            security: 'is_granted(\'ROLE_USER\')',
            input: CreateTeamInput::class,
            processor: CreateTeamProcessor::class,
        ),
        new Put(
            uriTemplate: '/teams/{id}',
            security: 'is_granted(\'ROLE_USER\')',
            input: UpdateTeamInput::class,
            provider: TeamItemProvider::class,
            processor: UpdateTeamProcessor::class,
        ),
        new Delete(
            uriTemplate: '/teams/{id}',
            security: 'is_granted(\'ROLE_ADMIN\')',
            provider: TeamItemProvider::class,
        ),
    ],
)]
class TeamResource
{
}

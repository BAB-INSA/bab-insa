<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Input\CreateTeamMatchInput;
use App\Dto\Input\UpdateMatchStatusInput;
use App\State\TeamMatch\CancelTeamMatchProcessor;
use App\State\TeamMatch\CreateTeamMatchProcessor;
use App\State\TeamMatch\RecentTeamMatchesProvider;
use App\State\TeamMatch\RejectTeamMatchProcessor;
use App\State\TeamMatch\TeamMatchCollectionProvider;
use App\State\TeamMatch\TeamMatchItemProvider;
use App\State\TeamMatch\UpdateTeamMatchStatusProcessor;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/team-matches', provider: TeamMatchCollectionProvider::class),
        new GetCollection(uriTemplate: '/team-matches/recent', provider: RecentTeamMatchesProvider::class),
        new Get(uriTemplate: '/team-matches/{id}', provider: TeamMatchItemProvider::class),
        new Post(
            uriTemplate: '/team-matches',
            security: 'is_granted(\'ROLE_USER\')',
            input: CreateTeamMatchInput::class,
            processor: CreateTeamMatchProcessor::class,
        ),
        new Patch(
            uriTemplate: '/team-matches/{id}',
            security: 'is_granted(\'ROLE_USER\')',
            input: UpdateMatchStatusInput::class,
            provider: TeamMatchItemProvider::class,
            processor: UpdateTeamMatchStatusProcessor::class,
        ),
        new Patch(
            uriTemplate: '/team-matches/{id}/reject',
            security: 'is_granted(\'ROLE_USER\')',
            provider: TeamMatchItemProvider::class,
            processor: RejectTeamMatchProcessor::class,
        ),
        new Patch(
            uriTemplate: '/team-matches/{id}/cancel',
            security: 'is_granted(\'ROLE_ADMIN\')',
            provider: TeamMatchItemProvider::class,
            processor: CancelTeamMatchProcessor::class,
        ),
    ],
)]
class TeamMatchResource
{
}

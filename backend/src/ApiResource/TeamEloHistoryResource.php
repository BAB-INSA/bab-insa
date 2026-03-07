<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\TeamEloHistory\RecentTeamEloHistoryProvider;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/team-elo-history/recent', provider: RecentTeamEloHistoryProvider::class),
    ],
)]
class TeamEloHistoryResource
{
}

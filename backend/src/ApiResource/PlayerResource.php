<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\Player\PlayerCollectionProvider;
use App\State\Player\PlayerEloHistoryProvider;
use App\State\Player\PlayerItemProvider;
use App\State\Player\PlayerMatchesProvider;
use App\State\Player\PlayerTeamEloHistoryProvider;
use App\State\Player\PlayerTeamsProvider;
use App\State\Player\TopPlayersProvider;
use App\State\Player\TopTeamPlayersProvider;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/players', provider: PlayerCollectionProvider::class),
        new GetCollection(uriTemplate: '/players/top', provider: TopPlayersProvider::class),
        new GetCollection(uriTemplate: '/players/top-teams', provider: TopTeamPlayersProvider::class),
        new Get(uriTemplate: '/players/{id}', provider: PlayerItemProvider::class),
        new GetCollection(uriTemplate: '/players/{id}/elo-history', provider: PlayerEloHistoryProvider::class),
        new GetCollection(uriTemplate: '/players/{id}/team-elo-history', provider: PlayerTeamEloHistoryProvider::class),
        new GetCollection(uriTemplate: '/players/{id}/matches', provider: PlayerMatchesProvider::class),
        new GetCollection(uriTemplate: '/players/{id}/teams', provider: PlayerTeamsProvider::class),
    ],
)]
class PlayerResource
{
}

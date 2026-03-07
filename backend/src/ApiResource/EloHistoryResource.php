<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\EloHistory\RecentEloHistoryProvider;
use App\State\Player\PlayerEloHistoryProvider;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/elo-history/recent', provider: RecentEloHistoryProvider::class),
        new GetCollection(uriTemplate: '/players/{id}/elo-history', provider: PlayerEloHistoryProvider::class),
    ],
)]
class EloHistoryResource
{
}

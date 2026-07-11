<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Stats\StatsProvider;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/stats', provider: StatsProvider::class),
    ],
)]
class StatsResource
{
}

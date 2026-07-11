<?php

declare(strict_types=1);

namespace App\Enum;

enum TournamentType: string
{
    case Solo = 'solo';
    case Team = 'team';
}

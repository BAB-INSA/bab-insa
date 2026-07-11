<?php

declare(strict_types=1);

namespace App\Enum;

enum TournamentStatus: string
{
    case Opened = 'opened';
    case Ongoing = 'ongoing';
    case Finished = 'finished';
}

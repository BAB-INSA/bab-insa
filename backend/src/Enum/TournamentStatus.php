<?php

namespace App\Enum;

enum TournamentStatus: string
{
    case Opened = 'opened';
    case Ongoing = 'ongoing';
    case Finished = 'finished';
}

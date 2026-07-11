<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class TournamentMinOutput
{
    public int $id;
    public string $name;
    public string $slug;
    public string $type;
    public string $status;
}

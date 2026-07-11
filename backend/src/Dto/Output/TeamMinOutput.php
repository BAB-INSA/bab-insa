<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class TeamMinOutput
{
    public int $id;
    public string $name;
    public string $slug;
    public float $eloRating;
}

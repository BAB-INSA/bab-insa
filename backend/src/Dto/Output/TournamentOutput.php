<?php

namespace App\Dto\Output;

final class TournamentOutput
{
    public int $id;
    public string $name;
    public string $slug;
    public string $type;
    public string $status;
    public ?string $description;
    public int $nbParticipants;
    public int $nbMatches;
    public string $createdAt;
    public string $updatedAt;
}

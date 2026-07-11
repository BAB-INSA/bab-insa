<?php

declare(strict_types=1);

namespace App\Dto\Input;

use App\Enum\TournamentType;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateTournamentInput
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['solo', 'team'])]
    public string $type;

    public ?string $description = null;

    public function typeEnum(): TournamentType
    {
        return TournamentType::from($this->type);
    }
}

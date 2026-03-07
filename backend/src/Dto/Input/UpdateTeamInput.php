<?php

namespace App\Dto\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateTeamInput
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $name;
}

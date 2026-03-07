<?php

namespace App\Dto\Input;

final class AdminUpdateUserInput
{
    /** @var string[]|null */
    public ?array $roles = null;

    public ?bool $enabled = null;
}

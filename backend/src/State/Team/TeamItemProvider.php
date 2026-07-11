<?php

declare(strict_types=1);

namespace App\State\Team;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TeamOutput;
use App\Repository\TeamRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<TeamOutput> */
final class TeamItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TeamOutput
    {
        $id   = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $team = $this->teamRepository->find($id);

        if ($team === null) {
            throw new NotFoundHttpException(sprintf('Team %d not found.', $id));
        }

        return $this->mapper->teamToOutput($team);
    }
}

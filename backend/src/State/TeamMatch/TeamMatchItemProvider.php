<?php

namespace App\State\TeamMatch;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TeamMatchOutput;
use App\Repository\TeamMatchRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<TeamMatchOutput> */
final class TeamMatchItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly TeamMatchRepository $teamMatchRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TeamMatchOutput
    {
        $id    = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $match = $this->teamMatchRepository->find($id);

        if ($match === null) {
            throw new NotFoundHttpException(sprintf('TeamMatch %d not found.', $id));
        }

        return $this->mapper->teamMatchToOutput($match);
    }
}

<?php

namespace App\State\Team;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TeamOutput;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<TeamOutput[]> */
final class PlayerTeamsProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly TeamRepository $teamRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return TeamOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $playerId = isset($uriVariables['playerId']) && is_scalar($uriVariables['playerId']) ? (int) $uriVariables['playerId'] : 0;
        $player   = $this->playerRepository->find($playerId);

        if ($player === null) {
            throw new NotFoundHttpException(sprintf('Player %d not found.', $playerId));
        }

        $teams = $this->teamRepository->findByPlayer($playerId);

        return array_map(fn ($team) => $this->mapper->teamToOutput($team), $teams);
    }
}

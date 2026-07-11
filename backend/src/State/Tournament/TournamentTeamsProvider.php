<?php

declare(strict_types=1);

namespace App\State\Tournament;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TeamOutput;
use App\Repository\TournamentRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<TeamOutput[]> */
final class TournamentTeamsProvider implements ProviderInterface
{
    public function __construct(
        private readonly TournamentRepository $tournamentRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return TeamOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $id         = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $tournament = $this->tournamentRepository->find($id);

        if ($tournament === null) {
            throw new NotFoundHttpException(sprintf('Tournament %d not found.', $id));
        }

        $teams = [];
        foreach ($tournament->getTournamentTeams() as $tt) {
            $teams[] = $this->mapper->teamToOutput($tt->getTeam());
        }

        return $teams;
    }
}

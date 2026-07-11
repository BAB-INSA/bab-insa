<?php

declare(strict_types=1);

namespace App\State\Tournament;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\TournamentRepository;
use App\Service\TournamentService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<mixed, null> */
final class LeaveTournamentProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TournamentService $tournamentService,
        private readonly TournamentRepository $tournamentRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $tournamentId = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $teamId       = isset($uriVariables['teamId']) && is_scalar($uriVariables['teamId']) ? (int) $uriVariables['teamId'] : 0;

        $tournament = $this->tournamentRepository->find($tournamentId);

        if ($tournament === null) {
            throw new NotFoundHttpException(sprintf('Tournament %d not found.', $tournamentId));
        }

        $this->tournamentService->leave($tournament, $teamId);

        return null;
    }
}

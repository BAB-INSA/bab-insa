<?php

namespace App\State\Tournament;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\MatchOutput;
use App\Repository\TournamentRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<MatchOutput[]> */
final class TournamentMatchesProvider implements ProviderInterface
{
    public function __construct(
        private readonly TournamentRepository $tournamentRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return MatchOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $id         = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $tournament = $this->tournamentRepository->find($id);

        if ($tournament === null) {
            throw new NotFoundHttpException(sprintf('Tournament %d not found.', $id));
        }

        $matches = [];
        foreach ($tournament->getMatches() as $match) {
            $matches[] = $this->mapper->matchToOutput($match);
        }

        return $matches;
    }
}

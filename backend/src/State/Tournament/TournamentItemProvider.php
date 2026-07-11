<?php

declare(strict_types=1);

namespace App\State\Tournament;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TournamentOutput;
use App\Repository\TournamentRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<TournamentOutput> */
final class TournamentItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly TournamentRepository $tournamentRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TournamentOutput
    {
        $id         = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $tournament = $this->tournamentRepository->find($id);

        if ($tournament === null) {
            throw new NotFoundHttpException(sprintf('Tournament %d not found.', $id));
        }

        return $this->mapper->tournamentToOutput($tournament);
    }
}

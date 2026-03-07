<?php

namespace App\State\Tournament;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TournamentOutput;
use App\Enum\TournamentStatus;
use App\Enum\TournamentType;
use App\Repository\TournamentRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<TournamentOutput[]> */
final class TournamentCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly TournamentRepository $tournamentRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return TournamentOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $context['request'] ?? null;
        $request = $request instanceof Request ? $request : null;
        $page    = $request !== null ? (int) ($request->query->get('page', 1)) : 1;
        $limit   = $request !== null ? (int) ($request->query->get('limit', 20)) : 20;

        $page  = max(1, $page);
        $limit = max(1, min(100, $limit));

        $status = null;
        $type   = null;

        if ($request !== null) {
            $statusParam = $request->query->get('status');
            if ($statusParam !== null && $statusParam !== '') {
                $status = TournamentStatus::tryFrom($statusParam);
            }

            $typeParam = $request->query->get('type');
            if ($typeParam !== null && $typeParam !== '') {
                $type = TournamentType::tryFrom($typeParam);
            }
        }

        $tournaments = $this->tournamentRepository->findFiltered($status, $type, $page, $limit);

        return array_map(fn ($t) => $this->mapper->tournamentToOutput($t), $tournaments);
    }
}

<?php

namespace App\State\TeamEloHistory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TeamEloHistoryOutput;
use App\Repository\TeamEloHistoryRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<TeamEloHistoryOutput[]> */
final class RecentTeamEloHistoryProvider implements ProviderInterface
{
    public function __construct(
        private readonly TeamEloHistoryRepository $teamEloHistoryRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return TeamEloHistoryOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $context['request'] ?? null;
        $limit   = $request instanceof Request ? (int) ($request->query->get('limit', 20)) : 20;
        $limit   = max(1, min(100, $limit));

        $history = $this->teamEloHistoryRepository->findRecent($limit);

        return array_map(fn ($h) => $this->mapper->teamEloHistoryToOutput($h), $history);
    }
}

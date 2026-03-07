<?php

namespace App\State\EloHistory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\EloHistoryOutput;
use App\Repository\EloHistoryRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<EloHistoryOutput[]> */
final class RecentEloHistoryProvider implements ProviderInterface
{
    public function __construct(
        private readonly EloHistoryRepository $eloHistoryRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return EloHistoryOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $context['request'] ?? null;
        $limit   = $request instanceof Request ? (int) ($request->query->get('limit', 20)) : 20;
        $limit   = max(1, min(100, $limit));

        $history = $this->eloHistoryRepository->findRecent($limit);

        return array_map(fn ($h) => $this->mapper->eloHistoryToOutput($h), $history);
    }
}

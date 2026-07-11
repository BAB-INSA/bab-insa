<?php

declare(strict_types=1);

namespace App\State\Team;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TeamOutput;
use App\Repository\TeamRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<TeamOutput> */
final class TeamCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $request = $context['request'] ?? null;
        $request = $request instanceof Request ? $request : null;
        $page    = $request !== null ? (int) ($request->query->get('page', 1)) : 1;
        $limit   = $request !== null ? (int) ($request->query->get('limit', 20)) : 20;

        $page  = max(1, $page);
        $limit = max(1, min(100, $limit));

        $teams = $this->teamRepository->findPaginated($page, $limit);
        $total = $this->teamRepository->countAll();
        $items = array_map(fn ($team) => $this->mapper->teamToOutput($team), $teams);

        return new TraversablePaginator(new \ArrayIterator($items), (float) $page, (float) $limit, (float) $total);
    }
}

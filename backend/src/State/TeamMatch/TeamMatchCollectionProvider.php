<?php

declare(strict_types=1);

namespace App\State\TeamMatch;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TeamMatchOutput;
use App\Repository\TeamMatchRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<TeamMatchOutput> */
final class TeamMatchCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly TeamMatchRepository $teamMatchRepository,
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

        $matches = $this->teamMatchRepository->findPaginated($page, $limit);
        $total   = $this->teamMatchRepository->countAll();
        $items   = array_map(fn ($match) => $this->mapper->teamMatchToOutput($match), $matches);

        return new TraversablePaginator(new \ArrayIterator($items), (float) $page, (float) $limit, (float) $total);
    }
}

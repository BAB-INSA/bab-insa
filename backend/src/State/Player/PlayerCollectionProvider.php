<?php

declare(strict_types=1);

namespace App\State\Player;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\PlayerOutput;
use App\Repository\PlayerRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<PlayerOutput> */
final class PlayerCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $request = $context['request'] ?? null;
        $request = $request instanceof Request ? $request : null;
        $page    = $request !== null ? (int) ($request->query->get('page', 1)) : 1;
        // `itemsPerPage` est le paramètre standard API Platform, `limit` celui de l'API Go
        $limit   = $request !== null ? (int) ($request->query->get('itemsPerPage', $request->query->get('limit', 20))) : 20;

        $page  = max(1, $page);
        $limit = max(1, min(100, $limit));

        $players = $this->playerRepository->findPaginated($page, $limit);
        $total   = $this->playerRepository->countAll();
        $items   = array_map(fn ($player) => $this->mapper->playerToOutput($player), $players);

        return new TraversablePaginator(new \ArrayIterator($items), (float) $page, (float) $limit, (float) $total);
    }
}

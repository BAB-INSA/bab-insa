<?php

declare(strict_types=1);

namespace App\State\Player;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\MatchOutput;
use App\Repository\PlayerRepository;
use App\Repository\SoloMatchRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<MatchOutput> */
final class PlayerMatchesProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly SoloMatchRepository $soloMatchRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $playerId = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $player   = $this->playerRepository->find($playerId);

        if ($player === null) {
            throw new NotFoundHttpException(sprintf('Player %d not found.', $playerId));
        }

        $request = $context['request'] ?? null;
        $request = $request instanceof Request ? $request : null;
        $page    = $request !== null ? (int) ($request->query->get('page', 1)) : 1;
        $limit   = $request !== null ? (int) ($request->query->get('limit', 20)) : 20;

        $page  = max(1, $page);
        $limit = max(1, min(100, $limit));

        $matches = $this->soloMatchRepository->findByPlayer($playerId, $page, $limit);
        $total   = $this->soloMatchRepository->countByPlayer($playerId);
        $items   = array_map(fn ($match) => $this->mapper->matchToOutput($match), $matches);

        return new TraversablePaginator(new \ArrayIterator($items), (float) $page, (float) $limit, (float) $total);
    }
}

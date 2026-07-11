<?php

declare(strict_types=1);

namespace App\State\Match;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\MatchOutput;
use App\Enum\MatchStatus;
use App\Repository\SoloMatchRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<MatchOutput> */
final class MatchCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly SoloMatchRepository $soloMatchRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $request = $context['request'] ?? null;
        $request = $request instanceof Request ? $request : null;

        $page    = $request !== null ? (int) ($request->query->get('page', 1)) : 1;
        $limit   = $request !== null ? (int) ($request->query->get('limit', 20)) : 20;
        $page    = max(1, $page);
        $limit   = max(1, min(100, $limit));

        $playerId   = null;
        $status     = null;
        $from       = null;
        $to         = null;

        if ($request !== null) {
            $playerIdParam = $request->query->get('player_id');
            if ($playerIdParam !== null && $playerIdParam !== '') {
                $playerId = (int) $playerIdParam;
            }

            $statusParam = $request->query->get('status');
            if ($statusParam !== null && $statusParam !== '') {
                $status = MatchStatus::tryFrom($statusParam);
            }

            $fromParam = $request->query->get('from');
            if ($fromParam !== null && $fromParam !== '') {
                $from = \DateTimeImmutable::createFromFormat('Y-m-d', $fromParam) ?: null;
            }

            $toParam = $request->query->get('to');
            if ($toParam !== null && $toParam !== '') {
                $to = \DateTimeImmutable::createFromFormat('Y-m-d', $toParam) ?: null;
            }
        }

        $matches = $this->soloMatchRepository->findFiltered($playerId, $status, $from, $to, $page, $limit);
        $total   = $this->soloMatchRepository->countFiltered($playerId, $status, $from, $to);
        $items   = array_map(fn ($match) => $this->mapper->matchToOutput($match), $matches);

        return new TraversablePaginator(new \ArrayIterator($items), (float) $page, (float) $limit, (float) $total);
    }
}

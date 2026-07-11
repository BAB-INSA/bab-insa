<?php

declare(strict_types=1);

namespace App\State\Player;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\EloHistoryOutput;
use App\Repository\EloHistoryRepository;
use App\Repository\PlayerRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<EloHistoryOutput[]> */
final class PlayerEloHistoryProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly EloHistoryRepository $eloHistoryRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return EloHistoryOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
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

        $history = $this->eloHistoryRepository->findByPlayer($playerId, $page, $limit);

        return array_map(fn ($h) => $this->mapper->eloHistoryToOutput($h), $history);
    }
}

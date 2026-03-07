<?php

namespace App\State\Player;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\PlayerOutput;
use App\Repository\PlayerRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<PlayerOutput[]> */
final class PlayerCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return PlayerOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $context['request'] ?? null;
        $request = $request instanceof Request ? $request : null;
        $page    = $request !== null ? (int) ($request->query->get('page', 1)) : 1;
        $limit   = $request !== null ? (int) ($request->query->get('limit', 20)) : 20;

        $page  = max(1, $page);
        $limit = max(1, min(100, $limit));

        $players = $this->playerRepository->findPaginated($page, $limit);

        return array_map(fn ($player) => $this->mapper->playerToOutput($player), $players);
    }
}

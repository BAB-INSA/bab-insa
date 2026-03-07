<?php

namespace App\State\Player;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\PlayerOutput;
use App\Repository\PlayerRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<PlayerOutput[]> */
final class TopTeamPlayersProvider implements ProviderInterface
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
        $limit   = $request instanceof Request ? (int) ($request->query->get('limit', 50)) : 50;
        $limit   = max(1, min(100, $limit));

        $players = $this->playerRepository->findTopByTeamElo($limit);

        return array_map(fn ($player) => $this->mapper->playerToOutput($player), $players);
    }
}

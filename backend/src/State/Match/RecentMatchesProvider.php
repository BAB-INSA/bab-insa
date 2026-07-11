<?php

declare(strict_types=1);

namespace App\State\Match;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\MatchOutput;
use App\Repository\SoloMatchRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<MatchOutput[]> */
final class RecentMatchesProvider implements ProviderInterface
{
    public function __construct(
        private readonly SoloMatchRepository $soloMatchRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return MatchOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $context['request'] ?? null;
        $limit   = $request instanceof Request ? (int) ($request->query->get('limit', 10)) : 10;
        $limit   = max(1, min(100, $limit));

        $matches = $this->soloMatchRepository->findRecent($limit);

        return array_map(fn ($match) => $this->mapper->matchToOutput($match), $matches);
    }
}

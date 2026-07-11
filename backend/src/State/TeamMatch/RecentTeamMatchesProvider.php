<?php

declare(strict_types=1);

namespace App\State\TeamMatch;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TeamMatchOutput;
use App\Repository\TeamMatchRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<TeamMatchOutput[]> */
final class RecentTeamMatchesProvider implements ProviderInterface
{
    public function __construct(
        private readonly TeamMatchRepository $teamMatchRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return TeamMatchOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $context['request'] ?? null;
        $limit   = $request instanceof Request ? (int) ($request->query->get('limit', 10)) : 10;
        $limit   = max(1, min(100, $limit));

        $matches = $this->teamMatchRepository->findRecent($limit);

        return array_map(fn ($match) => $this->mapper->teamMatchToOutput($match), $matches);
    }
}

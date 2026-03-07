<?php

namespace App\State\TeamMatch;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\TeamMatchOutput;
use App\Repository\TeamMatchRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<TeamMatchOutput[]> */
final class TeamMatchCollectionProvider implements ProviderInterface
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
        $request = $request instanceof Request ? $request : null;
        $page    = $request !== null ? (int) ($request->query->get('page', 1)) : 1;
        $limit   = $request !== null ? (int) ($request->query->get('limit', 20)) : 20;

        $page  = max(1, $page);
        $limit = max(1, min(100, $limit));

        $matches = $this->teamMatchRepository->findPaginated($page, $limit);

        return array_map(fn ($match) => $this->mapper->teamMatchToOutput($match), $matches);
    }
}

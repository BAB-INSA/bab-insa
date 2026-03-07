<?php

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\UserOutput;
use App\Repository\UserRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<UserOutput> */
final class UserCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $request = $context['request'] ?? null;
        $request = $request instanceof Request ? $request : null;
        $page    = $request !== null ? (int) ($request->query->get('page', 1)) : 1;
        $limit   = $request !== null ? (int) ($request->query->get('limit', 20)) : 20;
        $search  = $request !== null ? (string) ($request->query->get('search', '')) : '';

        $page  = max(1, $page);
        $limit = max(1, min(100, $limit));

        $users = $this->userRepository->searchPaginated($search, $page, $limit);
        $total = $this->userRepository->countSearch($search);
        $items = array_map(fn ($user) => $this->mapper->userToOutput($user), $users);

        return new TraversablePaginator(new \ArrayIterator($items), (float) $page, (float) $limit, (float) $total);
    }
}

<?php

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\UserOutput;
use App\Repository\UserRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<UserOutput[]> */
final class UserCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    /**
     * @return UserOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $context['request'] ?? null;
        $request = $request instanceof Request ? $request : null;
        $page    = $request !== null ? (int) ($request->query->get('page', 1)) : 1;
        $limit   = $request !== null ? (int) ($request->query->get('limit', 20)) : 20;
        $search  = $request !== null ? (string) ($request->query->get('search', '')) : '';

        $page  = max(1, $page);
        $limit = max(1, min(100, $limit));

        $users = $this->userRepository->searchPaginated($search, $page, $limit);

        return array_map(fn ($user) => $this->mapper->userToOutput($user), $users);
    }
}

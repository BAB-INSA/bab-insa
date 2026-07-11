<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\UserOutput;
use App\Repository\UserRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<UserOutput> */
final class UserItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserOutput
    {
        $id   = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $user = $this->userRepository->find($id);

        if ($user === null) {
            throw new NotFoundHttpException(sprintf('User %d not found.', $id));
        }

        return $this->mapper->userToOutput($user);
    }
}

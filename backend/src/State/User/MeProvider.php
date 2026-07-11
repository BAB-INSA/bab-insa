<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\UserOutput;
use App\Entity\User;
use App\Service\DtoMapper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/** @implements ProviderInterface<UserOutput> */
final class MeProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserOutput
    {
        $user = $this->security->getUser();

        if ($user === null) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required.');
        }

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found.');
        }

        return $this->mapper->userToOutput($user);
    }
}

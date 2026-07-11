<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Input\UpdateUserInput;
use App\Dto\Output\UserOutput;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DtoMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<UpdateUserInput, UserOutput> */
final class UpdateUserProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly DtoMapper $mapper,
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserOutput
    {
        /** @var UpdateUserInput $data */
        $violations = $this->validator->validate($data);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(implode(', ', $messages));
        }

        $userId = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $user   = $this->userRepository->find($userId);

        if ($user === null) {
            throw new NotFoundHttpException(sprintf('User %d not found.', $userId));
        }

        // Only allow users to update their own profile
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User || $currentUser->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException('You are not allowed to update this user.');
        }

        if ($data->email !== null) {
            $user->setEmail($data->email);
        }

        if ($data->username !== null) {
            $user->setUsername($data->username);
        }

        $this->em->flush();

        return $this->mapper->userToOutput($user);
    }
}

<?php

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Input\AdminUpdateUserInput;
use App\Dto\Output\UserOutput;
use App\Repository\UserRepository;
use App\Service\DtoMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<AdminUpdateUserInput, UserOutput> */
final class AdminUpdateUserProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly DtoMapper $mapper,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserOutput
    {
        /** @var AdminUpdateUserInput $data */
        $violations = $this->validator->validate($data);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath().': '.$violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(implode(', ', $messages));
        }

        $userId = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $user   = $this->userRepository->find($userId);

        if ($user === null) {
            throw new NotFoundHttpException(sprintf('User %d not found.', $userId));
        }

        if ($data->roles !== null) {
            $user->setRoles($data->roles);
        }

        if ($data->enabled !== null) {
            $user->setEnabled($data->enabled);
        }

        $this->em->flush();

        return $this->mapper->userToOutput($user);
    }
}

<?php

declare(strict_types=1);

namespace App\State\Team;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Input\UpdateTeamInput;
use App\Dto\Output\TeamOutput;
use App\Repository\TeamRepository;
use App\Service\DtoMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<UpdateTeamInput, TeamOutput> */
final class UpdateTeamProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly EntityManagerInterface $em,
        private readonly DtoMapper $mapper,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TeamOutput
    {
        /** @var UpdateTeamInput $data */
        $violations = $this->validator->validate($data);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(implode(', ', $messages));
        }

        $teamId = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $team   = $this->teamRepository->find($teamId);

        if ($team === null) {
            throw new NotFoundHttpException(sprintf('Team %d not found.', $teamId));
        }

        $team->setName($data->name);
        $this->em->flush();

        return $this->mapper->teamToOutput($team);
    }
}

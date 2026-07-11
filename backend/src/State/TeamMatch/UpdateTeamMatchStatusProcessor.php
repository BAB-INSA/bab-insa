<?php

declare(strict_types=1);

namespace App\State\TeamMatch;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Input\UpdateMatchStatusInput;
use App\Dto\Output\TeamMatchOutput;
use App\Repository\TeamMatchRepository;
use App\Service\TeamMatchService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<UpdateMatchStatusInput, TeamMatchOutput> */
final class UpdateTeamMatchStatusProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TeamMatchService $teamMatchService,
        private readonly TeamMatchRepository $teamMatchRepository,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TeamMatchOutput
    {
        /** @var UpdateMatchStatusInput $data */
        $violations = $this->validator->validate($data);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(implode(', ', $messages));
        }

        $matchId = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $match   = $this->teamMatchRepository->find($matchId);

        if ($match === null) {
            throw new NotFoundHttpException(sprintf('TeamMatch %d not found.', $matchId));
        }

        return $this->teamMatchService->updateStatus($match, $data);
    }
}

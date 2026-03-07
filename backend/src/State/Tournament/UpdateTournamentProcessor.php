<?php

namespace App\State\Tournament;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Input\UpdateTournamentInput;
use App\Dto\Output\TournamentOutput;
use App\Repository\TournamentRepository;
use App\Service\TournamentService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<UpdateTournamentInput, TournamentOutput> */
final class UpdateTournamentProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TournamentService $tournamentService,
        private readonly TournamentRepository $tournamentRepository,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TournamentOutput
    {
        /** @var UpdateTournamentInput $data */
        $violations = $this->validator->validate($data);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath().': '.$violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(implode(', ', $messages));
        }

        $tournamentId = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $tournament   = $this->tournamentRepository->find($tournamentId);

        if ($tournament === null) {
            throw new NotFoundHttpException(sprintf('Tournament %d not found.', $tournamentId));
        }

        return $this->tournamentService->update($tournament, $data);
    }
}

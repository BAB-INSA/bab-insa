<?php

declare(strict_types=1);

namespace App\State\Tournament;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Input\CreateTournamentInput;
use App\Dto\Output\TournamentOutput;
use App\Service\TournamentService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<CreateTournamentInput, TournamentOutput> */
final class CreateTournamentProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TournamentService $tournamentService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TournamentOutput
    {
        /** @var CreateTournamentInput $data */
        $violations = $this->validator->validate($data);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(implode(', ', $messages));
        }

        return $this->tournamentService->create($data);
    }
}

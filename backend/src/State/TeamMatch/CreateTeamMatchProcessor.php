<?php

namespace App\State\TeamMatch;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Input\CreateTeamMatchInput;
use App\Dto\Output\TeamMatchOutput;
use App\Service\TeamMatchService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<CreateTeamMatchInput, TeamMatchOutput> */
final class CreateTeamMatchProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TeamMatchService $teamMatchService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TeamMatchOutput
    {
        /** @var CreateTeamMatchInput $data */
        $violations = $this->validator->validate($data);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath().': '.$violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(implode(', ', $messages));
        }

        return $this->teamMatchService->create($data);
    }
}

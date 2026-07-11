<?php

declare(strict_types=1);

namespace App\State\Match;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Input\CreateMatchInput;
use App\Dto\Output\MatchOutput;
use App\Service\MatchService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<CreateMatchInput, MatchOutput> */
final class CreateMatchProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MatchService $matchService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MatchOutput
    {
        /** @var CreateMatchInput $data */
        $violations = $this->validator->validate($data);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(implode(', ', $messages));
        }

        return $this->matchService->create($data);
    }
}

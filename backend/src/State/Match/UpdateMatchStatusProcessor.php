<?php

namespace App\State\Match;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Input\UpdateMatchStatusInput;
use App\Dto\Output\MatchOutput;
use App\Entity\Player;
use App\Entity\User;
use App\Repository\PlayerRepository;
use App\Repository\SoloMatchRepository;
use App\Service\MatchService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<UpdateMatchStatusInput, MatchOutput> */
final class UpdateMatchStatusProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MatchService $matchService,
        private readonly SoloMatchRepository $soloMatchRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MatchOutput
    {
        /** @var UpdateMatchStatusInput $data */
        $violations = $this->validator->validate($data);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath().': '.$violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(implode(', ', $messages));
        }

        $matchId = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $match   = $this->soloMatchRepository->find($matchId);

        if ($match === null) {
            throw new NotFoundHttpException(sprintf('Match %d not found.', $matchId));
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new NotFoundHttpException('Current user not found.');
        }

        $player = $this->playerRepository->findOneBy(['user' => $user]);
        if (!$player instanceof Player) {
            throw new NotFoundHttpException('Player profile not found for current user.');
        }

        return $this->matchService->updateStatus($match, $data, $player);
    }
}

<?php

namespace App\State\Team;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Input\CreateTeamInput;
use App\Dto\Output\TeamOutput;
use App\Entity\Player;
use App\Entity\Team;
use App\Repository\PlayerRepository;
use App\Service\DtoMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<CreateTeamInput, TeamOutput> */
final class CreateTeamProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly EntityManagerInterface $em,
        private readonly DtoMapper $mapper,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TeamOutput
    {
        /** @var CreateTeamInput $data */
        $violations = $this->validator->validate($data);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath().': '.$violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(implode(', ', $messages));
        }

        $player1 = $this->findPlayer($data->player1Id);
        $player2 = $this->findPlayer($data->player2Id);

        $team = new Team();
        $team->setPlayer1($player1);
        $team->setPlayer2($player2);
        $team->setName($data->name);

        $this->em->persist($team);
        $this->em->flush();

        return $this->mapper->teamToOutput($team);
    }

    private function findPlayer(int $id): Player
    {
        $player = $this->playerRepository->find($id);
        if ($player === null) {
            throw new NotFoundHttpException(sprintf('Player %d not found.', $id));
        }

        return $player;
    }
}

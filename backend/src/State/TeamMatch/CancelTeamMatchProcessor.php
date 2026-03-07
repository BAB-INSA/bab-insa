<?php

namespace App\State\TeamMatch;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Output\TeamMatchOutput;
use App\Enum\MatchStatus;
use App\Repository\TeamMatchRepository;
use App\Service\DtoMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<mixed, TeamMatchOutput> */
final class CancelTeamMatchProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TeamMatchRepository $teamMatchRepository,
        private readonly EntityManagerInterface $em,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TeamMatchOutput
    {
        $matchId = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $match   = $this->teamMatchRepository->find($matchId);

        if ($match === null) {
            throw new NotFoundHttpException(sprintf('TeamMatch %d not found.', $matchId));
        }

        $match->setStatus(MatchStatus::Cancelled);
        $this->em->flush();

        return $this->mapper->teamMatchToOutput($match);
    }
}

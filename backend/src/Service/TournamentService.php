<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\CreateTournamentInput;
use App\Dto\Input\UpdateTournamentInput;
use App\Dto\Output\TournamentOutput;
use App\Entity\Tournament;
use App\Entity\TournamentTeam;
use App\Repository\TeamRepository;
use App\Repository\TournamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TournamentService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TournamentRepository $tournamentRepository,
        private readonly TeamRepository $teamRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function create(CreateTournamentInput $input): TournamentOutput
    {
        $tournament = new Tournament();
        $tournament->setName($input->name);
        $tournament->setType($input->typeEnum());
        $tournament->setDescription($input->description);

        $this->em->persist($tournament);
        $this->em->flush();

        return $this->mapper->tournamentToOutput($tournament);
    }

    public function update(Tournament $tournament, UpdateTournamentInput $input): TournamentOutput
    {
        if ($input->name !== null) {
            $tournament->setName($input->name);
        }
        if ($input->statusEnum() !== null) {
            $tournament->setStatus($input->statusEnum());
        }
        if ($input->description !== null) {
            $tournament->setDescription($input->description);
        }

        $this->em->flush();

        return $this->mapper->tournamentToOutput($tournament);
    }

    public function join(Tournament $tournament, int $teamId): TournamentOutput
    {
        $team = $this->teamRepository->find($teamId);
        if ($team === null) {
            throw new NotFoundHttpException(sprintf('Team %d not found.', $teamId));
        }

        // Vérifier que l'équipe n'est pas déjà inscrite
        foreach ($tournament->getTournamentTeams() as $tt) {
            if ($tt->getTeam()->getId() === $team->getId()) {
                throw new BadRequestHttpException('Team already joined this tournament.');
            }
        }

        $tt = new TournamentTeam();
        $tt->setTournament($tournament);
        $tt->setTeam($team);

        $tournament->setNbParticipants($tournament->getNbParticipants() + 1);

        $this->em->persist($tt);
        $this->em->flush();

        return $this->mapper->tournamentToOutput($tournament);
    }

    public function leave(Tournament $tournament, int $teamId): void
    {
        foreach ($tournament->getTournamentTeams() as $tt) {
            if ($tt->getTeam()->getId() === $teamId) {
                $tournament->setNbParticipants(max(0, $tournament->getNbParticipants() - 1));
                $this->em->remove($tt);
                $this->em->flush();

                return;
            }
        }

        throw new NotFoundHttpException(sprintf('Team %d is not in this tournament.', $teamId));
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\CreateTeamMatchInput;
use App\Dto\Input\UpdateMatchStatusInput;
use App\Dto\Output\TeamMatchOutput;
use App\Entity\Team;
use App\Entity\TeamEloHistory;
use App\Entity\TeamMatch;
use App\Enum\MatchStatus;
use App\Repository\TeamMatchRepository;
use App\Repository\TeamRepository;
use App\Repository\TournamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TeamMatchService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TeamMatchRepository $teamMatchRepository,
        private readonly TeamRepository $teamRepository,
        private readonly TournamentRepository $tournamentRepository,
        private readonly EloCalculatorService $eloCalculator,
        private readonly PlayerRankingService $rankingService,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function create(CreateTeamMatchInput $input): TeamMatchOutput
    {
        $team1  = $this->findTeam($input->team1Id);
        $team2  = $this->findTeam($input->team2Id);
        $winner = $this->findTeam($input->winnerTeamId);

        if ($team1->getId() === $team2->getId()) {
            throw new BadRequestHttpException('A team cannot play against itself.');
        }
        if ($winner->getId() !== $team1->getId() && $winner->getId() !== $team2->getId()) {
            throw new BadRequestHttpException('Winner must be one of the two teams.');
        }

        $match = new TeamMatch();
        $match->setTeam1($team1);
        $match->setTeam2($team2);
        $match->setWinnerTeam($winner);
        $match->setStatus(MatchStatus::Confirmed);
        $match->setConfirmedAt(new \DateTimeImmutable());

        if ($input->tournamentId !== null) {
            $tournament = $this->tournamentRepository->find($input->tournamentId);
            if ($tournament !== null) {
                $match->setTournament($tournament);
            }
        }

        $this->em->persist($match);
        $this->applyTeamEloChanges($match, $team1, $team2, $winner);
        $this->em->flush();

        $this->rankingService->updateTeamRanks();

        return $this->mapper->teamMatchToOutput($match);
    }

    public function updateStatus(TeamMatch $match, UpdateMatchStatusInput $input): TeamMatchOutput
    {
        $newStatus = $input->getStatus();

        if ($newStatus === MatchStatus::Confirmed) {
            $match->setStatus(MatchStatus::Confirmed);
            $match->setConfirmedAt(new \DateTimeImmutable());

            $winner = $match->getWinnerTeam();
            if ($winner !== null) {
                $this->applyTeamEloChanges($match, $match->getTeam1(), $match->getTeam2(), $winner);
            }
        } elseif ($newStatus === MatchStatus::Rejected) {
            $match->setStatus(MatchStatus::Rejected);
        } elseif ($newStatus === MatchStatus::Cancelled) {
            $match->setStatus(MatchStatus::Cancelled);
        }

        $this->em->flush();

        if ($newStatus === MatchStatus::Confirmed) {
            $this->rankingService->updateTeamRanks();
        }

        return $this->mapper->teamMatchToOutput($match);
    }

    private function applyTeamEloChanges(TeamMatch $match, Team $team1, Team $team2, Team $winner): void
    {
        $team1Won  = $winner->getId() === $team1->getId();
        $team1Avg  = ($team1->getPlayer1()->getTeamEloRating() + $team1->getPlayer2()->getTeamEloRating()) / 2;
        $team2Avg  = ($team2->getPlayer1()->getTeamEloRating() + $team2->getPlayer2()->getTeamEloRating()) / 2;

        // Met à jour ELO des 4 joueurs
        foreach ([[$team1, $team1Won, $team2Avg], [$team2, !$team1Won, $team1Avg]] as [$team, $won, $opponentAvg]) {
            foreach ([$team->getPlayer1(), $team->getPlayer2()] as $player) {
                $before = $player->getTeamEloRating();
                $after  = $this->eloCalculator->calculateTeam($before, $opponentAvg, $won);

                $player->setTeamEloRating($after);
                $player->setTeamTotalMatches($player->getTeamTotalMatches() + 1);
                if ($won) {
                    $player->setTeamWins($player->getTeamWins() + 1);
                } else {
                    $player->setTeamLosses($player->getTeamLosses() + 1);
                }

                $h = new TeamEloHistory();
                $h->setPlayer($player);
                $h->setTeamMatch($match);
                $h->setEloBefore($before);
                $h->setEloAfter($after);
                $h->setEloChange(round($after - $before, 2));
                $this->em->persist($h);
            }

            // Met à jour ELO de l'équipe (moyenne des deux joueurs après update)
            $p1After = $team->getPlayer1()->getTeamEloRating();
            $p2After = $team->getPlayer2()->getTeamEloRating();
            $team->setEloRating(round(($p1After + $p2After) / 2, 2));
            $team->setTotalMatches($team->getTotalMatches() + 1);
            if ($won) {
                $team->setWins($team->getWins() + 1);
            } else {
                $team->setLosses($team->getLosses() + 1);
            }
        }
    }

    private function findTeam(int $id): Team
    {
        $team = $this->teamRepository->find($id);
        if ($team === null) {
            throw new NotFoundHttpException(sprintf('Team %d not found.', $id));
        }

        return $team;
    }
}

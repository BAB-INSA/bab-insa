<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\CreateMatchInput;
use App\Dto\Input\UpdateMatchStatusInput;
use App\Dto\Output\MatchOutput;
use App\Entity\EloHistory;
use App\Entity\Player;
use App\Entity\SoloMatch;
use App\Enum\MatchStatus;
use App\Repository\PlayerRepository;
use App\Repository\SoloMatchRepository;
use App\Repository\TournamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MatchService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SoloMatchRepository $matchRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly TournamentRepository $tournamentRepository,
        private readonly EloCalculatorService $eloCalculator,
        private readonly PlayerRankingService $rankingService,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function create(CreateMatchInput $input): MatchOutput
    {
        $player1 = $this->findPlayer($input->player1Id);
        $player2 = $this->findPlayer($input->player2Id);
        $winner  = $this->findPlayer($input->winnerId);

        if ($player1->getId() === $player2->getId()) {
            throw new BadRequestHttpException('A player cannot play against themselves.');
        }

        if ($winner->getId() !== $player1->getId() && $winner->getId() !== $player2->getId()) {
            throw new BadRequestHttpException('Winner must be one of the two players.');
        }

        // Comme en Go : le match est créé en pending, l'ELO et les stats
        // ne sont appliqués qu'à la confirmation.
        $match = new SoloMatch();
        $match->setPlayer1($player1);
        $match->setPlayer2($player2);
        $match->setWinner($winner);
        $match->setStatus(MatchStatus::Pending);

        if ($input->tournamentId !== null) {
            $tournament = $this->tournamentRepository->find($input->tournamentId);
            if ($tournament !== null) {
                $match->setTournament($tournament);
            }
        }

        $this->em->persist($match);
        $this->em->flush();

        return $this->mapper->matchToOutput($match);
    }

    public function updateStatus(SoloMatch $match, UpdateMatchStatusInput $input, Player $currentPlayer): MatchOutput
    {
        $newStatus = $input->statusEnum();

        // Comme en Go : seuls les participants peuvent modifier un match,
        // et uniquement tant qu'il est pending.
        $isParticipant = $currentPlayer->getId() === $match->getPlayer1()->getId()
            || $currentPlayer->getId() === $match->getPlayer2()->getId();
        if (!$isParticipant) {
            throw new BadRequestHttpException('Only a participant can update a match.');
        }

        if ($match->getStatus() !== MatchStatus::Pending) {
            throw new BadRequestHttpException('Match is not pending.');
        }

        if ($newStatus === MatchStatus::Confirmed) {
            $match->setStatus(MatchStatus::Confirmed);
            $match->setConfirmedAt(new \DateTimeImmutable());

            $winner  = $match->getWinner();
            $player1 = $match->getPlayer1();
            $player2 = $match->getPlayer2();
            if ($winner !== null) {
                $this->applyEloChanges($match, $player1, $player2, $winner);
            }
        } elseif ($newStatus === MatchStatus::Rejected) {
            $match->setStatus(MatchStatus::Rejected);
        } elseif ($newStatus === MatchStatus::Cancelled) {
            $match->setStatus(MatchStatus::Cancelled);
        }

        $this->em->flush();

        if ($newStatus === MatchStatus::Confirmed) {
            $this->rankingService->updateSoloRanks();
        }

        return $this->mapper->matchToOutput($match);
    }

    private function applyEloChanges(SoloMatch $match, Player $player1, Player $player2, Player $winner): void
    {
        $p1Won = $winner->getId() === $player1->getId();

        $p1Before = $player1->getEloRating();
        $p2Before = $player2->getEloRating();

        $p1After = $this->eloCalculator->calculate($p1Before, $p2Before, $p1Won);
        $p2After = $this->eloCalculator->calculate($p2Before, $p1Before, !$p1Won);

        $player1->setEloRating($p1After);
        $player1->setTotalMatches($player1->getTotalMatches() + 1);
        if ($p1Won) {
            $player1->setWins($player1->getWins() + 1);
        } else {
            $player1->setLosses($player1->getLosses() + 1);
        }

        $player2->setEloRating($p2After);
        $player2->setTotalMatches($player2->getTotalMatches() + 1);
        if (!$p1Won) {
            $player2->setWins($player2->getWins() + 1);
        } else {
            $player2->setLosses($player2->getLosses() + 1);
        }

        // EloHistory player1
        $h1 = new EloHistory();
        $h1->setPlayer($player1);
        $h1->setMatch($match);
        $h1->setEloBefore($p1Before);
        $h1->setEloAfter($p1After);
        $h1->setEloChange(round($p1After - $p1Before, 2));
        $h1->setOpponent($player2);
        $this->em->persist($h1);

        // EloHistory player2
        $h2 = new EloHistory();
        $h2->setPlayer($player2);
        $h2->setMatch($match);
        $h2->setEloBefore($p2Before);
        $h2->setEloAfter($p2After);
        $h2->setEloChange(round($p2After - $p2Before, 2));
        $h2->setOpponent($player1);
        $this->em->persist($h2);
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

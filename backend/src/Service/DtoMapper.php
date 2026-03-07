<?php

namespace App\Service;

use App\Dto\Output\EloHistoryOutput;
use App\Dto\Output\MatchOutput;
use App\Dto\Output\PlayerMinOutput;
use App\Dto\Output\PlayerOutput;
use App\Dto\Output\StatsOutput;
use App\Dto\Output\TeamEloHistoryOutput;
use App\Dto\Output\TeamMatchOutput;
use App\Dto\Output\TeamMinOutput;
use App\Dto\Output\TeamOutput;
use App\Dto\Output\TournamentMinOutput;
use App\Dto\Output\TournamentOutput;
use App\Dto\Output\UserOutput;
use App\Entity\EloHistory;
use App\Entity\Player;
use App\Entity\SoloMatch;
use App\Entity\Team;
use App\Entity\TeamEloHistory;
use App\Entity\TeamMatch;
use App\Entity\Tournament;
use App\Entity\User;

/**
 * Convertit les entités Doctrine en Output DTOs.
 */
class DtoMapper
{
    public function playerToOutput(Player $player): PlayerOutput
    {
        $dto = new PlayerOutput();
        $dto->id              = (int) $player->getId();
        $dto->username        = $player->getUsername();
        $dto->slug            = $player->getUser()->getSlug();
        $dto->eloRating       = $player->getEloRating();
        $dto->rank            = $player->getRank();
        $dto->totalMatches    = $player->getTotalMatches();
        $dto->wins            = $player->getWins();
        $dto->losses          = $player->getLosses();
        $dto->teamEloRating   = $player->getTeamEloRating();
        $dto->teamRank        = $player->getTeamRank();
        $dto->teamTotalMatches = $player->getTeamTotalMatches();
        $dto->teamWins        = $player->getTeamWins();
        $dto->teamLosses      = $player->getTeamLosses();
        $dto->createdAt       = $player->getCreatedAt()?->format(\DateTimeInterface::ATOM) ?? '';

        return $dto;
    }

    public function playerToMin(Player $player): PlayerMinOutput
    {
        $dto = new PlayerMinOutput();
        $dto->id         = (int) $player->getId();
        $dto->username   = $player->getUsername();
        $dto->slug       = $player->getUser()->getSlug();
        $dto->eloRating  = $player->getEloRating();
        $dto->rank       = $player->getRank();

        return $dto;
    }

    public function matchToOutput(SoloMatch $match): MatchOutput
    {
        $dto = new MatchOutput();
        $dto->id          = (int) $match->getId();
        $dto->player1     = $this->playerToMin($match->getPlayer1());
        $dto->player2     = $this->playerToMin($match->getPlayer2());
        $dto->winner      = $match->getWinner() ? $this->playerToMin($match->getWinner()) : null;
        $dto->status      = $match->getStatus()->value;
        $dto->tournament  = $match->getTournament() ? $this->tournamentToMin($match->getTournament()) : null;
        $dto->confirmedAt = $match->getConfirmedAt()?->format(\DateTimeInterface::ATOM);
        $dto->createdAt   = $match->getCreatedAt()?->format(\DateTimeInterface::ATOM) ?? '';
        $dto->updatedAt   = $match->getUpdatedAt()?->format(\DateTimeInterface::ATOM) ?? '';

        return $dto;
    }

    public function teamToOutput(Team $team): TeamOutput
    {
        $dto = new TeamOutput();
        $dto->id           = (int) $team->getId();
        $dto->name         = $team->getName();
        $dto->slug         = $team->getSlug();
        $dto->player1      = $this->playerToMin($team->getPlayer1());
        $dto->player2      = $this->playerToMin($team->getPlayer2());
        $dto->eloRating    = $team->getEloRating();
        $dto->totalMatches = $team->getTotalMatches();
        $dto->wins         = $team->getWins();
        $dto->losses       = $team->getLosses();
        $dto->createdAt    = $team->getCreatedAt()?->format(\DateTimeInterface::ATOM) ?? '';

        return $dto;
    }

    public function teamToMin(Team $team): TeamMinOutput
    {
        $dto = new TeamMinOutput();
        $dto->id        = (int) $team->getId();
        $dto->name      = $team->getName();
        $dto->slug      = $team->getSlug();
        $dto->eloRating = $team->getEloRating();

        return $dto;
    }

    public function teamMatchToOutput(TeamMatch $match): TeamMatchOutput
    {
        $dto = new TeamMatchOutput();
        $dto->id          = (int) $match->getId();
        $dto->team1       = $this->teamToMin($match->getTeam1());
        $dto->team2       = $this->teamToMin($match->getTeam2());
        $dto->winnerTeam  = $match->getWinnerTeam() ? $this->teamToMin($match->getWinnerTeam()) : null;
        $dto->status      = $match->getStatus()->value;
        $dto->tournament  = $match->getTournament() ? $this->tournamentToMin($match->getTournament()) : null;
        $dto->confirmedAt = $match->getConfirmedAt()?->format(\DateTimeInterface::ATOM);
        $dto->createdAt   = $match->getCreatedAt()?->format(\DateTimeInterface::ATOM) ?? '';
        $dto->updatedAt   = $match->getUpdatedAt()?->format(\DateTimeInterface::ATOM) ?? '';

        return $dto;
    }

    public function tournamentToOutput(Tournament $tournament): TournamentOutput
    {
        $dto = new TournamentOutput();
        $dto->id             = (int) $tournament->getId();
        $dto->name           = $tournament->getName();
        $dto->slug           = $tournament->getSlug();
        $dto->type           = $tournament->getType()->value;
        $dto->status         = $tournament->getStatus()->value;
        $dto->description    = $tournament->getDescription();
        $dto->nbParticipants = $tournament->getNbParticipants();
        $dto->nbMatches      = $tournament->getNbMatches();
        $dto->createdAt      = $tournament->getCreatedAt()?->format(\DateTimeInterface::ATOM) ?? '';
        $dto->updatedAt      = $tournament->getUpdatedAt()?->format(\DateTimeInterface::ATOM) ?? '';

        return $dto;
    }

    public function tournamentToMin(Tournament $tournament): TournamentMinOutput
    {
        $dto = new TournamentMinOutput();
        $dto->id     = (int) $tournament->getId();
        $dto->name   = $tournament->getName();
        $dto->slug   = $tournament->getSlug();
        $dto->type   = $tournament->getType()->value;
        $dto->status = $tournament->getStatus()->value;

        return $dto;
    }

    public function eloHistoryToOutput(EloHistory $history): EloHistoryOutput
    {
        $dto = new EloHistoryOutput();
        $dto->id         = (int) $history->getId();
        $dto->playerId   = (int) $history->getPlayer()->getId();
        $dto->matchId    = (int) $history->getMatch()->getId();
        $dto->eloBefore  = $history->getEloBefore();
        $dto->eloAfter   = $history->getEloAfter();
        $dto->eloChange  = $history->getEloChange();
        $dto->opponentId = $history->getOpponent()?->getId();
        $dto->createdAt  = $history->getCreatedAt()->format(\DateTimeInterface::ATOM);

        return $dto;
    }

    public function teamEloHistoryToOutput(TeamEloHistory $history): TeamEloHistoryOutput
    {
        $dto = new TeamEloHistoryOutput();
        $dto->id          = (int) $history->getId();
        $dto->playerId    = (int) $history->getPlayer()->getId();
        $dto->teamMatchId = (int) $history->getTeamMatch()->getId();
        $dto->eloBefore   = $history->getEloBefore();
        $dto->eloAfter    = $history->getEloAfter();
        $dto->eloChange   = $history->getEloChange();
        $dto->createdAt   = $history->getCreatedAt()->format(\DateTimeInterface::ATOM);

        return $dto;
    }

    public function userToOutput(User $user): UserOutput
    {
        $dto = new UserOutput();
        $dto->id          = (int) $user->getId();
        $dto->email       = $user->getEmail();
        $dto->username    = $user->getUsername();
        $dto->slug        = $user->getSlug();
        $dto->roles       = $user->getRoles();
        $dto->enabled     = $user->isEnabled();
        $dto->lastLogin   = $user->getLastLogin()?->format(\DateTimeInterface::ATOM);
        $dto->nbConnexion = $user->getNbConnexion();
        $dto->createdAt   = $user->getCreatedAt()?->format(\DateTimeInterface::ATOM) ?? '';

        return $dto;
    }

    /**
     * @param array<string, int> $counts
     */
    public function countsToStats(array $counts): StatsOutput
    {
        $dto = new StatsOutput();
        $dto->totalPlayers      = $counts['players'] ?? 0;
        $dto->totalMatches      = $counts['matches'] ?? 0;
        $dto->totalTeams        = $counts['teams'] ?? 0;
        $dto->totalTeamMatches  = $counts['team_matches'] ?? 0;
        $dto->totalTournaments  = $counts['tournaments'] ?? 0;

        return $dto;
    }
}

<?php

namespace App\Entity;
use App\Repository\TeamMatchRepository;

use App\Enum\MatchStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: TeamMatchRepository::class)]
#[ORM\Table(name: 'team_matches')]
#[ORM\Index(columns: ['team1_id'], name: 'idx_team_matches_team1')]
#[ORM\Index(columns: ['team2_id'], name: 'idx_team_matches_team2')]
#[ORM\Index(columns: ['winner_team_id'], name: 'idx_team_matches_winner')]
#[ORM\Index(columns: ['status'], name: 'idx_team_matches_status')]
#[ORM\Index(columns: ['tournament_id'], name: 'idx_team_matches_tournament')]
#[ORM\Index(columns: ['created_at'], name: 'idx_team_matches_created_at')]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
class TeamMatch
{
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Team $team1;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Team $team2;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Team $winnerTeam = null;

    #[ORM\Column(enumType: MatchStatus::class, options: ['default' => 'pending'])]
    private MatchStatus $status = MatchStatus::Pending;

    #[ORM\ManyToOne(targetEntity: Tournament::class, inversedBy: 'teamMatches')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Tournament $tournament = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $confirmedAt = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeam1(): Team
    {
        return $this->team1;
    }

    public function setTeam1(Team $team1): static
    {
        $this->team1 = $team1;

        return $this;
    }

    public function getTeam2(): Team
    {
        return $this->team2;
    }

    public function setTeam2(Team $team2): static
    {
        $this->team2 = $team2;

        return $this;
    }

    public function getWinnerTeam(): ?Team
    {
        return $this->winnerTeam;
    }

    public function setWinnerTeam(?Team $winnerTeam): static
    {
        $this->winnerTeam = $winnerTeam;

        return $this;
    }

    public function getStatus(): MatchStatus
    {
        return $this->status;
    }

    public function setStatus(MatchStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): static
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getConfirmedAt(): ?\DateTimeImmutable
    {
        return $this->confirmedAt;
    }

    public function setConfirmedAt(?\DateTimeImmutable $confirmedAt): static
    {
        $this->confirmedAt = $confirmedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isPending(): bool
    {
        return MatchStatus::Pending === $this->status;
    }
}

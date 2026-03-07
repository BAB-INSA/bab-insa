<?php

namespace App\Entity;
use App\Repository\TeamEloHistoryRepository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamEloHistoryRepository::class)]
#[ORM\Table(name: 'team_elo_history')]
#[ORM\Index(columns: ['player_id'], name: 'idx_team_elo_history_player')]
#[ORM\Index(columns: ['team_match_id'], name: 'idx_team_elo_history_match')]
#[ORM\Index(columns: ['created_at'], name: 'idx_team_elo_history_created_at')]
class TeamEloHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Le joueur concerné par ce changement d'ELO équipe.
     */
    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'teamEloHistories')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Player $player;

    #[ORM\ManyToOne(targetEntity: TeamMatch::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private TeamMatch $teamMatch;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $eloBefore;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $eloAfter;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $eloChange;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getTeamMatch(): TeamMatch
    {
        return $this->teamMatch;
    }

    public function setTeamMatch(TeamMatch $teamMatch): static
    {
        $this->teamMatch = $teamMatch;

        return $this;
    }

    public function getEloBefore(): float
    {
        return (float) $this->eloBefore;
    }

    public function setEloBefore(float $eloBefore): static
    {
        $this->eloBefore = (string) $eloBefore;

        return $this;
    }

    public function getEloAfter(): float
    {
        return (float) $this->eloAfter;
    }

    public function setEloAfter(float $eloAfter): static
    {
        $this->eloAfter = (string) $eloAfter;

        return $this;
    }

    public function getEloChange(): float
    {
        return (float) $this->eloChange;
    }

    public function setEloChange(float $eloChange): static
    {
        $this->eloChange = (string) $eloChange;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

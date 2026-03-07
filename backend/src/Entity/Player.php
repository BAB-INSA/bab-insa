<?php

namespace App\Entity;
use App\Repository\PlayerRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\Table(name: 'players')]
#[ORM\Index(columns: ['elo_rating'], name: 'idx_players_elo')]
#[ORM\Index(columns: ['team_elo_rating'], name: 'idx_players_team_elo')]
#[ORM\Index(columns: ['rank'], name: 'idx_players_rank')]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
class Player
{
    use SoftDeleteableEntity;

    /**
     * L'ID du Player est le même que celui du User (derived identifier).
     * Cela reproduit fidèlement le modèle Go où player.id == user.id.
     */
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 100)]
    private string $username;

    // --- Stats solo ---
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => '1200.00'])]
    private string $eloRating = '1200.00';

    #[ORM\Column(options: ['default' => 0])]
    private int $rank = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $totalMatches = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $wins = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $losses = 0;

    // --- Stats équipe ---
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => '1200.00'])]
    private string $teamEloRating = '1200.00';

    #[ORM\Column(options: ['default' => 0])]
    private int $teamRank = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $teamTotalMatches = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $teamWins = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $teamLosses = 0;

    /** @var Collection<int, EloHistory> */
    #[ORM\OneToMany(targetEntity: EloHistory::class, mappedBy: 'player', cascade: ['remove'])]
    private Collection $eloHistories;

    /** @var Collection<int, TeamEloHistory> */
    #[ORM\OneToMany(targetEntity: TeamEloHistory::class, mappedBy: 'player', cascade: ['remove'])]
    private Collection $teamEloHistories;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->eloHistories = new ArrayCollection();
        $this->teamEloHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->user->getId();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEloRating(): float
    {
        return (float) $this->eloRating;
    }

    public function setEloRating(float $eloRating): static
    {
        $this->eloRating = (string) $eloRating;

        return $this;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function getTotalMatches(): int
    {
        return $this->totalMatches;
    }

    public function setTotalMatches(int $totalMatches): static
    {
        $this->totalMatches = $totalMatches;

        return $this;
    }

    public function getWins(): int
    {
        return $this->wins;
    }

    public function setWins(int $wins): static
    {
        $this->wins = $wins;

        return $this;
    }

    public function getLosses(): int
    {
        return $this->losses;
    }

    public function setLosses(int $losses): static
    {
        $this->losses = $losses;

        return $this;
    }

    public function getTeamEloRating(): float
    {
        return (float) $this->teamEloRating;
    }

    public function setTeamEloRating(float $teamEloRating): static
    {
        $this->teamEloRating = (string) $teamEloRating;

        return $this;
    }

    public function getTeamRank(): int
    {
        return $this->teamRank;
    }

    public function setTeamRank(int $teamRank): static
    {
        $this->teamRank = $teamRank;

        return $this;
    }

    public function getTeamTotalMatches(): int
    {
        return $this->teamTotalMatches;
    }

    public function setTeamTotalMatches(int $teamTotalMatches): static
    {
        $this->teamTotalMatches = $teamTotalMatches;

        return $this;
    }

    public function getTeamWins(): int
    {
        return $this->teamWins;
    }

    public function setTeamWins(int $teamWins): static
    {
        $this->teamWins = $teamWins;

        return $this;
    }

    public function getTeamLosses(): int
    {
        return $this->teamLosses;
    }

    public function setTeamLosses(int $teamLosses): static
    {
        $this->teamLosses = $teamLosses;

        return $this;
    }

    /** @return Collection<int, EloHistory> */
    public function getEloHistories(): Collection
    {
        return $this->eloHistories;
    }

    /** @return Collection<int, TeamEloHistory> */
    public function getTeamEloHistories(): Collection
    {
        return $this->teamEloHistories;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\Table(name: 'teams')]
#[ORM\Index(columns: ['elo_rating'], name: 'idx_teams_elo')]
#[ORM\Index(columns: ['player1_id'], name: 'idx_teams_player1')]
#[ORM\Index(columns: ['player2_id'], name: 'idx_teams_player2')]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
class Team
{
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Player $player1;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Player $player2;

    #[ORM\Column(length: 255)]
    private string $name;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => '1200.00'])]
    private string $eloRating = '1200.00';

    #[ORM\Column(options: ['default' => 0])]
    private int $totalMatches = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $wins = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $losses = 0;

    /** @var Collection<int, TournamentTeam> */
    #[ORM\OneToMany(targetEntity: TournamentTeam::class, mappedBy: 'team', cascade: ['remove'])]
    private Collection $tournamentTeams;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->tournamentTeams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer1(): Player
    {
        return $this->player1;
    }

    public function setPlayer1(Player $player1): static
    {
        $this->player1 = $player1;

        return $this;
    }

    public function getPlayer2(): Player
    {
        return $this->player2;
    }

    public function setPlayer2(Player $player2): static
    {
        $this->player2 = $player2;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
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

    /** @return Collection<int, TournamentTeam> */
    public function getTournamentTeams(): Collection
    {
        return $this->tournamentTeams;
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

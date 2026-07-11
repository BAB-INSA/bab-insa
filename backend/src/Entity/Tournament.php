<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TournamentRepository;
use App\Enum\TournamentStatus;
use App\Enum\TournamentType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
#[ORM\Table(name: 'tournaments')]
#[ORM\Index(columns: ['status'], name: 'idx_tournaments_status')]
#[ORM\Index(columns: ['type'], name: 'idx_tournaments_type')]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
class Tournament
{
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(enumType: TournamentType::class)]
    private TournamentType $type;

    #[ORM\Column(enumType: TournamentStatus::class, options: ['default' => 'opened'])]
    private TournamentStatus $status = TournamentStatus::Opened;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $nbParticipants = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $nbMatches = 0;

    /** @var Collection<int, TournamentTeam> */
    #[ORM\OneToMany(targetEntity: TournamentTeam::class, mappedBy: 'tournament', cascade: ['persist', 'remove'])]
    private Collection $tournamentTeams;

    /** @var Collection<int, SoloMatch> */
    #[ORM\OneToMany(targetEntity: SoloMatch::class, mappedBy: 'tournament')]
    private Collection $matches;

    /** @var Collection<int, TeamMatch> */
    #[ORM\OneToMany(targetEntity: TeamMatch::class, mappedBy: 'tournament')]
    private Collection $teamMatches;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->tournamentTeams = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->teamMatches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): TournamentType
    {
        return $this->type;
    }

    public function setType(TournamentType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): TournamentStatus
    {
        return $this->status;
    }

    public function setStatus(TournamentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNbParticipants(): int
    {
        return $this->nbParticipants;
    }

    public function setNbParticipants(int $nbParticipants): static
    {
        $this->nbParticipants = $nbParticipants;

        return $this;
    }

    public function getNbMatches(): int
    {
        return $this->nbMatches;
    }

    public function setNbMatches(int $nbMatches): static
    {
        $this->nbMatches = $nbMatches;

        return $this;
    }

    /** @return Collection<int, TournamentTeam> */
    public function getTournamentTeams(): Collection
    {
        return $this->tournamentTeams;
    }

    /** @return Collection<int, SoloMatch> */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    /** @return Collection<int, TeamMatch> */
    public function getTeamMatches(): Collection
    {
        return $this->teamMatches;
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

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SoloMatchRepository;
use App\Enum\MatchStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * Représente un match solo entre deux joueurs.
 * Classe nommée SoloMatch car "match" est un mot réservé en PHP 8.
 * Table en base : "matches" (compatibilité avec l'API Go).
 */
#[ORM\Entity(repositoryClass: SoloMatchRepository::class)]
#[ORM\Table(name: 'matches')]
#[ORM\Index(columns: ['player1_id'], name: 'idx_matches_player1')]
#[ORM\Index(columns: ['player2_id'], name: 'idx_matches_player2')]
#[ORM\Index(columns: ['winner_id'], name: 'idx_matches_winner')]
#[ORM\Index(columns: ['status'], name: 'idx_matches_status')]
#[ORM\Index(columns: ['tournament_id'], name: 'idx_matches_tournament')]
#[ORM\Index(columns: ['created_at'], name: 'idx_matches_created_at')]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
class SoloMatch
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

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Player $winner = null;

    #[ORM\Column(enumType: MatchStatus::class, options: ['default' => 'pending'])]
    private MatchStatus $status = MatchStatus::Pending;

    #[ORM\ManyToOne(targetEntity: Tournament::class, inversedBy: 'matches')]
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

    public function getWinner(): ?Player
    {
        return $this->winner;
    }

    public function setWinner(?Player $winner): static
    {
        $this->winner = $winner;

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

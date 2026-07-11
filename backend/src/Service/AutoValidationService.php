<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\MatchStatus;
use App\Repository\SoloMatchRepository;
use App\Repository\TeamMatchRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service d'auto-validation des matches pending.
 * Après 48h, les matches en attente sont automatiquement confirmés.
 * Identique au comportement de l'API Go (auto_validation_service.go).
 */
class AutoValidationService
{
    private const PENDING_TTL_HOURS = 48;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SoloMatchRepository $soloMatchRepository,
        private readonly TeamMatchRepository $teamMatchRepository,
        private readonly MatchService $matchService,
        private readonly TeamMatchService $teamMatchService,
        private readonly PlayerRankingService $rankingService,
    ) {
    }

    /** @return array{solo: int, team: int} */
    public function validatePending(): array
    {
        $threshold = new \DateTimeImmutable(sprintf('-%d hours', self::PENDING_TTL_HOURS));
        $validated = ['solo' => 0, 'team' => 0];

        // Solo matches
        $pendingMatches = $this->soloMatchRepository->findPendingOlderThan($threshold);
        foreach ($pendingMatches as $match) {
            $match->setStatus(MatchStatus::Confirmed);
            $match->setConfirmedAt(new \DateTimeImmutable());
            ++$validated['solo'];
        }

        // Team matches
        $pendingTeamMatches = $this->teamMatchRepository->findPendingOlderThan($threshold);
        foreach ($pendingTeamMatches as $match) {
            $match->setStatus(MatchStatus::Confirmed);
            $match->setConfirmedAt(new \DateTimeImmutable());
            ++$validated['team'];
        }

        if ($validated['solo'] > 0 || $validated['team'] > 0) {
            $this->em->flush();
            $this->rankingService->updateSoloRanks();
            $this->rankingService->updateTeamRanks();
        }

        return $validated;
    }
}

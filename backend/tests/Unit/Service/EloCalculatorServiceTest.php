<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\EloCalculatorService;
use PHPUnit\Framework\TestCase;

/**
 * 7.5 — Vérifier la cohérence ELO (algo identique au Go).
 */
class EloCalculatorServiceTest extends TestCase
{
    private EloCalculatorService $service;

    protected function setUp(): void
    {
        $this->service = new EloCalculatorService();
    }

    // K=32, MIN=1200, formule standard

    public function testWinnerGainsEloLoserLoses(): void
    {
        // ELO > 1200 pour ne pas déclencher le plancher MIN_ELO côté perdant
        $winnerNewElo = $this->service->calculate(1500.0, 1500.0, true);
        $loserNewElo  = $this->service->calculate(1500.0, 1500.0, false);

        $this->assertGreaterThan(1500.0, $winnerNewElo);
        $this->assertLessThan(1500.0, $loserNewElo);
    }

    public function testEloChangeIsSymmetric(): void
    {
        // ELO > 1200 pour ne pas déclencher le plancher MIN_ELO côté perdant
        $winnerElo = $this->service->calculate(1500.0, 1500.0, true);
        $loserElo  = $this->service->calculate(1500.0, 1500.0, false);

        $winnerGain = $winnerElo - 1500.0;
        $loserLoss  = 1500.0 - $loserElo;

        // La somme des changements est nulle (K * (1-0.5) + K * (0-0.5) = 0)
        $this->assertEqualsWithDelta($winnerGain, $loserLoss, 0.01);
    }

    public function testEloNeverFallsBelowMinimum(): void
    {
        // Joueur très faible (1200) perd contre très fort (2000)
        $loserNewElo = $this->service->calculate(1200.0, 2000.0, false);

        $this->assertGreaterThanOrEqual(1200.0, $loserNewElo);
    }

    public function testStrongerPlayerGainsLessWhenWinning(): void
    {
        // Joueur fort (1800) bat joueur faible (1200) → gain attendu faible
        $strongWin  = $this->service->calculate(1800.0, 1200.0, true);
        $gainStrong = $strongWin - 1800.0;

        // Joueur faible (1200) bat joueur fort (1800) → gain attendu élevé
        $weakWin  = $this->service->calculate(1200.0, 1800.0, true);
        $gainWeak = $weakWin - 1200.0;

        $this->assertGreaterThan($gainStrong, $gainWeak);
    }

    public function testEqualEloResultsInHalfKChange(): void
    {
        // Quand les ELO sont égaux, expected = 0.5, gain = K * (1 - 0.5) = 16
        $winnerElo = $this->service->calculate(1200.0, 1200.0, true);

        $this->assertEqualsWithDelta(1216.0, $winnerElo, 0.01);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('eloProvider')]
    public function testCalculateIsConsistentWithGoAlgorithm(
        float $winnerElo,
        float $loserElo,
        float $expectedWinnerNewElo,
        float $expectedLoserNewElo,
    ): void {
        $newWinnerElo = $this->service->calculate($winnerElo, $loserElo, true);
        $newLoserElo  = $this->service->calculate($loserElo, $winnerElo, false);

        $this->assertEqualsWithDelta($expectedWinnerNewElo, $newWinnerElo, 0.1);
        $this->assertEqualsWithDelta($expectedLoserNewElo, $newLoserElo, 0.1);
    }

    /** @return array<string, array{float, float, float, float}> */
    public static function eloProvider(): array
    {
        return [
            // winnerElo, loserElo, expectedWinner, expectedLoser
            // Valeurs issues de l'algo Go : change = math.Round(K * (actual - expected))
            // K=32, égaux → +16 / -16
            'equal elo'            => [1200.0, 1200.0, 1216.0, 1200.0], // loser min=1200
            // fort bat faible : change = round(0.98) = +1
            'strong beats weak'    => [1800.0, 1200.0, 1801.0, 1200.0], // loser min=1200
            // faible bat fort : change = round(31.02) = ±31
            'weak beats strong'    => [1200.0, 1800.0, 1231.0, 1769.0],
        ];
    }
}

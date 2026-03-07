<?php

namespace App\Service;

/**
 * Calcul ELO fidèle à l'implémentation Go (packages/core/utils/elo.go).
 *
 * K = 32, ELO minimum = 1200
 * expectedScore = 1 / (1 + 10^((opponent - player) / 400))
 * newElo = max(MIN_ELO, player + K * (actualScore - expectedScore))
 */
class EloCalculatorService
{
    private const K_FACTOR = 32;
    private const MIN_ELO  = 1200.0;

    /**
     * Calcule le nouvel ELO d'un joueur après une partie.
     *
     * @param float $playerElo   ELO actuel du joueur
     * @param float $opponentElo ELO actuel de l'adversaire
     * @param bool  $won         Le joueur a-t-il gagné ?
     */
    public function calculate(float $playerElo, float $opponentElo, bool $won): float
    {
        $expected  = 1.0 / (1.0 + 10 ** (($opponentElo - $playerElo) / 400.0));
        $actual    = $won ? 1.0 : 0.0;
        $newElo    = $playerElo + self::K_FACTOR * ($actual - $expected);

        return max(self::MIN_ELO, round($newElo, 2));
    }

    /**
     * Calcule le nouvel ELO d'un joueur dans un match en équipe.
     * L'adversaire est la moyenne ELO des deux joueurs de l'équipe adverse.
     *
     * @param float $playerElo   ELO équipe du joueur
     * @param float $teamAvgElo  ELO équipe moyen de l'équipe adverse
     * @param bool  $won         L'équipe du joueur a-t-elle gagné ?
     */
    public function calculateTeam(float $playerElo, float $teamAvgElo, bool $won): float
    {
        return $this->calculate($playerElo, $teamAvgElo, $won);
    }

    public function getMinElo(): float
    {
        return self::MIN_ELO;
    }
}

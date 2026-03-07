import type { Player } from '@/features/core/types/player'

export function usePlayer() {
  const getWinRate = (player: Player): number => {
    if (player.totalMatches === 0) return 0
    return Math.round((player.wins / player.totalMatches) * 100 * 10) / 10
  }

  const getLosses = (player: Player): number => {
    return player.losses
  }

  const getWinLossRecord = (player: Player): string => {
    return `${player.wins}V / ${player.losses}D`
  }

  const getEloDisplay = (player: Player): string => {
    return Math.round(player.eloRating).toString()
  }

  return {
    getWinRate,
    getLosses,
    getWinLossRecord,
    getEloDisplay
  }
}
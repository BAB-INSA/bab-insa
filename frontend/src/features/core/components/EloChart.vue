<!-- src/features/core/components/EloChart.vue -->
<template>
  <div class="w-full">
    <div class="mb-4">
      <h3 class="text-lg font-semibold mb-2">
        Évolution du rating ELO {{ matchType === 'team' ? '2v2' : '1v1' }}
      </h3>
      <p class="text-sm text-muted-foreground">
        Historique des performances {{ matchType === 'team' ? 'en équipe' : 'en solo' }} sur les derniers matchs
      </p>
    </div>
    
    <div class="bg-card border rounded-lg p-4">
      <div v-if="isLoading" class="flex items-center justify-center h-64">
        <div class="text-sm text-muted-foreground">Chargement du graphique...</div>
      </div>
      
      <div v-else-if="hasData && chartData" class="h-64">
        <Line :data="chartData" :options="chartOptions" />
      </div>
      
      <div v-else class="flex flex-col items-center justify-center h-64 text-center">
        <div class="text-muted-foreground mb-2">
          <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <h4 class="text-sm font-medium mb-1">Aucune donnée disponible</h4>
        <p class="text-xs text-muted-foreground">
          Ce joueur n'a pas encore d'historique de matchs
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { Line } from 'vue-chartjs'
import type { TooltipItem } from 'chart.js'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

// Enregistrement des composants Chart.js
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

import type { EloChartEntry } from '@/features/core/types/player'

// Props
interface Props {
  playerId: number
  eloHistory?: EloChartEntry[]
  isLoading?: boolean
  matchType?: 'solo' | 'team'
}

const props = withDefaults(defineProps<Props>(), {
  eloHistory: () => [],
  isLoading: false,
  matchType: 'solo'
})

// State
const isDarkMode = ref(false)

// Computed
const hasData = computed(() => props.eloHistory && props.eloHistory.length > 0)

const chartData = computed(() => {
  if (!hasData.value) return null

  // Trier par date
  const sortedHistory = [...props.eloHistory].sort((a, b) => 
    new Date(a.date).getTime() - new Date(b.date).getTime()
  )

  return {
    labels: sortedHistory.map(entry => {
      const date = new Date(entry.date)
      return date.toLocaleDateString('fr-FR', { 
        day: '2-digit', 
        month: '2-digit' 
      })
    }),
    datasets: [
      {
        label: 'ELO Rating',
        data: sortedHistory.map(entry => entry.elo),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 2,
        pointBackgroundColor: 'rgb(59, 130, 246)',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        tension: 0.3,
        fill: true
      }
    ]
  }
})

const chartOptions = computed(() => {
  const minElo = hasData.value && props.eloHistory.length > 0 
    ? Math.min(...props.eloHistory.map(e => e.elo)) 
    : 1000
  const maxElo = hasData.value && props.eloHistory.length > 0 
    ? Math.max(...props.eloHistory.map(e => e.elo)) 
    : 1500

  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      intersect: false,
      mode: 'index' as const,
    },
    plugins: {
      legend: {
        display: false
      },
      tooltip: {
        backgroundColor: isDarkMode.value ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.9)',
        titleColor: isDarkMode.value ? '#ffffff' : '#000000',
        bodyColor: isDarkMode.value ? '#ffffff' : '#000000',
        borderColor: isDarkMode.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 8,
        callbacks: {
          title: (context: TooltipItem<'line'>[]) => {
            const index = context[0].dataIndex
            if (!props.eloHistory[index]) return ''
            const entry = props.eloHistory[index]
            const date = new Date(entry.date)
            return date.toLocaleDateString('fr-FR', { 
              weekday: 'long',
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            })
          },
          label: (context: TooltipItem<'line'>) => {
            return `ELO: ${context.parsed.y}`
          },
          afterLabel: (context: TooltipItem<'line'>) => {
            const index = context.dataIndex
            if (!props.eloHistory[index]) return ''
            
            const eloChange = props.eloHistory[index].eloChange
            
            if (eloChange > 0) {
              return `↗ +${eloChange} points`
            } else if (eloChange < 0) {
              return `↘ ${eloChange} points`
            } else {
              return '= Aucun changement'
            }
          }
        }
      }
    },
    scales: {
      x: {
        grid: {
          color: isDarkMode.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
        },
        ticks: {
          color: isDarkMode.value ? 'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)',
          maxTicksLimit: 10
        }
      },
      y: {
        beginAtZero: false,
        min: Math.max(1000, minElo - 50),
        max: maxElo + 50,
        grid: {
          color: isDarkMode.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
        },
        ticks: {
          color: isDarkMode.value ? 'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)',
          callback: function(value: string | number) {
            return Math.round(Number(value)).toString()
          }
        }
      }
    }
  }
})

// Détection du mode sombre
const detectDarkMode = () => {
  isDarkMode.value = document.documentElement.classList.contains('dark')
}

// Watchers
watch(() => props.eloHistory, () => {
  // Réagir aux changements de données
}, { deep: true })

onMounted(() => {
  detectDarkMode()
  
  // Observer les changements de thème
  const observer = new MutationObserver(detectDarkMode)
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
  })
  
  // Nettoyer l'observer
  return () => observer.disconnect()
})
</script>
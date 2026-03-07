<!-- src/views/rules/Rules.fr.vue -->
<script setup lang="ts">
import { Shield, AlertTriangle, Users, MessageSquare, Ban, CheckCircle } from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle
} from '@/shared/components/ui/card'
import { Badge } from '@/shared/components/ui/badge'

// Rules data for French
const rulesData = {
  general: {
    title: 'Règles Générales',
    icon: Shield,
    color: 'text-blue-600',
    bgColor: 'bg-blue-50',
    rules: [
      {
        id: 'respect',
        title: 'Respect et fair-play',
        description: 'Traitez tous les membres avec respect, que ce soit sur la plateforme ou lors des parties. Le fair-play est la valeur fondamentale de notre association.',
        severity: 'high'
      },
      {
        id: 'insa-community',
        title: 'Communauté INSA',
        description: 'L\'association est ouverte prioritairement aux étudiants et personnels de l\'INSA de Rouen. Les invités doivent être parrainés par un membre.',
        severity: 'medium'
      },
      {
        id: 'appropriate-behavior',
        title: 'Comportement approprié',
        description: 'Maintenez un comportement respectueux en toutes circonstances. Les insultes, provocations ou comportements antisportifs ne sont pas tolérés.',
        severity: 'high'
      },
      {
        id: 'equipment-care',
        title: 'Respect du matériel',
        description: 'Prenez soin des tables de baby-foot et du matériel de l\'association. Tout dégât volontaire sera sanctionné.',
        severity: 'medium'
      }
    ]
  },
  community: {
    title: 'Vie Associative',
    icon: Users,
    color: 'text-green-600',
    bgColor: 'bg-green-50',
    rules: [
      {
        id: 'welcoming-newcomers',
        title: 'Accueil des nouveaux',
        description: 'Aidez les nouveaux membres à s\'intégrer, expliquez les règles du baby-foot et partagez vos techniques. L\'entraide fait notre force.',
        severity: 'low'
      },
      {
        id: 'participation',
        title: 'Participation active',
        description: 'Participez aux tournois, événements et activités de l\'association. Plus nous sommes actifs, plus l\'association est dynamique.',
        severity: 'low'
      },
      {
        id: 'constructive-feedback',
        title: 'Critiques constructives',
        description: 'Vos retours sur l\'organisation, les tournois ou la plateforme sont les bienvenus, mais doivent être formulés de manière constructive.',
        severity: 'low'
      },
      {
        id: 'report-issues',
        title: 'Signalement des problèmes',
        description: 'Signalez tout comportement inapproprié ou problème technique aux responsables de l\'association plutôt que de réagir impulsivement.',
        severity: 'medium'
      }
    ]
  },
  platform: {
    title: 'Utilisation de la Plateforme',
    icon: MessageSquare,
    color: 'text-purple-600',
    bgColor: 'bg-purple-50',
    rules: [
      {
        id: 'match-reporting',
        title: 'Déclaration des matchs',
        description: 'Déclarez vos matchs de manière honnête et complète. Les scores trichés faussent le classement ELO et nuisent à tous.',
        severity: 'high'
      },
      {
        id: 'profile-accuracy',
        title: 'Profil authentique',
        description: 'Utilisez votre vraie identité dans votre profil. Les faux comptes perturbent l\'organisation des tournois.',
        severity: 'medium'
      },
      {
        id: 'respectful-communication',
        title: 'Communication respectueuse',
        description: 'Gardez un ton respectueux dans vos échanges sur la plateforme. Les provocations et insultes sont sanctionnées.',
        severity: 'high'
      },
      {
        id: 'no-cheating',
        title: 'Pas de triche',
        description: 'Toute forme de triche (manipulation des scores, création de faux matchs, etc.) est formellement interdite.',
        severity: 'high'
      }
    ]
  },
  sanctions: {
    title: 'Sanctions et Discipline',
    icon: Ban,
    color: 'text-red-600',
    bgColor: 'bg-red-50',
    rules: [
      {
        id: 'warning-system',
        title: 'Échelle des sanctions',
        description: '1er manquement : avertissement. 2ème : suspension d\'une semaine. 3ème : exclusion temporaire d\'un mois. Récidive : exclusion définitive.',
        severity: 'high'
      },
      {
        id: 'immediate-exclusion',
        title: 'Exclusion immédiate',
        description: 'Violence physique, harcèlement grave, dégradation volontaire du matériel ou triche répétée entraînent une exclusion immédiate.',
        severity: 'high'
      },
      {
        id: 'tournament-sanctions',
        title: 'Sanctions en tournoi',
        description: 'Comportement antisportif en tournoi : exclusion du tournoi en cours et interdiction de participation au tournoi suivant.',
        severity: 'medium'
      },
      {
        id: 'appeal-process',
        title: 'Droit de recours',
        description: 'Toute sanction peut être contestée auprès du bureau de l\'association. Contactez contact@bab-insa.fr avec vos arguments.',
        severity: 'medium'
      }
    ]
  }
}

const getSeverityBadge = (severity: string) => {
  switch (severity) {
    case 'high':
      return { variant: 'destructive' as const, text: 'Strict' }
    case 'medium':
      return { variant: 'default' as const, text: 'Important' }
    case 'low':
      return { variant: 'secondary' as const, text: 'Recommandé' }
    default:
      return { variant: 'outline' as const, text: 'Info' }
  }
}
</script>

<template>
  <div class="container mx-auto max-w-4xl px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="flex justify-center mb-4">
        <div class="bg-primary/10 rounded-full p-3">
          <Shield class="h-8 w-8 text-primary" />
        </div>
      </div>
      <h1 class="text-3xl font-bold mb-2">Règlement de l'Association</h1>
      <p class="text-muted-foreground text-lg">
        Ensemble, cultivons l'esprit sportif et la convivialité autour du baby-foot
      </p>
    </div>

    <!-- Important Notice -->
    <Card class="mb-8 border-amber-200 bg-amber-50">
      <CardContent class="pt-6">
        <div class="flex items-start gap-3">
          <AlertTriangle class="h-6 w-6 text-amber-600 flex-shrink-0 mt-0.5" />
          <div>
            <h3 class="font-semibold text-amber-800 mb-2">Important à savoir</h3>
            <p class="text-amber-700 text-sm">
              Ce règlement s'applique à tous les membres de l'association BAB'INSA et utilisateurs
              de la plateforme. En rejoignant l'association, vous acceptez de respecter ces règles.
              Toute violation peut entraîner des sanctions allant de l'avertissement à l'exclusion.
            </p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Rules Sections -->
    <div class="space-y-8">
      <div
          v-for="(section, key) in rulesData"
          :key="key"
          class="space-y-4"
      >
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-3">
              <div :class="[section.bgColor, 'p-2 rounded-lg']">
                <component :is="section.icon" :class="[section.color, 'h-6 w-6']" />
              </div>
              {{ section.title }}
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div
                v-for="rule in section.rules"
                :key="rule.id"
                class="border rounded-lg p-4 hover:bg-muted/50 transition-colors"
            >
              <div class="flex items-start justify-between gap-4 mb-2">
                <h4 class="font-semibold">{{ rule.title }}</h4>
                <Badge
                    :variant="getSeverityBadge(rule.severity).variant"
                    class="flex-shrink-0"
                >
                  {{ getSeverityBadge(rule.severity).text }}
                </Badge>
              </div>
              <p class="text-muted-foreground text-sm leading-relaxed">
                {{ rule.description }}
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Footer -->
    <Card class="mt-8">
      <CardContent class="pt-6">
        <div class="text-center">
          <div class="flex justify-center mb-4">
            <CheckCircle class="h-8 w-8 text-green-600" />
          </div>
          <h3 class="text-lg font-semibold mb-2">Merci de faire partie de BAB'INSA</h3>
          <p class="text-muted-foreground mb-4">
            En respectant ces règles, vous contribuez à maintenir l'esprit sportif et convivial qui fait la richesse de notre association.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
            <span class="text-muted-foreground">
              <strong>Dernière mise à jour :</strong> {{ new Date().toLocaleDateString('fr-FR') }}
            </span>
            <span class="hidden sm:inline text-muted-foreground">•</span>
            <a
                href="/contact"
                class="text-primary hover:underline font-medium"
            >
              Une question ? Contactez-nous
            </a>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<style scoped>
.container {
  scroll-behavior: smooth;
}
</style>

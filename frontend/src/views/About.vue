<!-- src/views/About.vue -->
<script setup lang="ts">
import { Heart, Users, Target, Code, Shield, Award, TrendingUp } from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle
} from '@/shared/components/ui/card'
import { Badge } from '@/shared/components/ui/badge'

// About sections data for French
const aboutData = {
  mission: {
    title: 'Notre Mission',
    icon: Target,
    color: 'text-blue-600',
    bgColor: 'bg-blue-50',
    content: `
      <p>Nous créons une communauté dynamique autour du baby-foot à l'INSA de Rouen, où étudiants et personnels peuvent se retrouver pour partager leur passion du jeu et créer des liens durables.</p>
      <p>Notre objectif est de développer la pratique du baby-foot sur le campus, organiser des tournois réguliers et maintenir un classement ELO pour stimuler la compétition amicale entre tous les participants.</p>
    `
  },
  story: {
    title: 'Notre Histoire',
    icon: Heart,
    color: 'text-red-600',
    bgColor: 'bg-red-50',
    content: `
      <p>Fondée en octobre 2025 par une bande d'étudiants passionnés de baby-foot à l'INSA de Rouen, notre association est née du constat simple : il manquait une vraie structure pour organiser et dynamiser la pratique du baby-foot sur le campus.</p>
      <p>Ce qui a commencé par quelques parties improvisées entre amis s'est rapidement transformé en une véritable communauté. Face à l'engouement grandissant, nous avons décidé de créer cette plateforme pour officialiser les matchs, tenir un classement et organiser des événements.</p>
      <p>Aujourd'hui, nous rassemblons déjà de nombreux joueurs réguliers et notre ambition est de faire du baby-foot un incontournable de la vie étudiante à l'INSA de Rouen.</p>
    `
  },
  values: {
    title: 'Nos Valeurs',
    icon: Shield,
    color: 'text-green-600',
    bgColor: 'bg-green-50',
    content: `
      <p>Nos valeurs fondamentales guident l'esprit de notre association :</p>
      <ul>
        <li><strong>Fair-play :</strong> Respect des règles et de l'adversaire dans chaque partie</li>
        <li><strong>Convivialité :</strong> Créer des liens et partager des moments amicaux autour du baby-foot</li>
        <li><strong>Inclusivité :</strong> Ouverture à tous les niveaux, du débutant au joueur confirmé</li>
        <li><strong>Passion :</strong> Cultiver l'amour du jeu et transmettre notre enthousiasme</li>
        <li><strong>Esprit d'équipe :</strong> Encourager la solidarité et l'entraide entre les membres</li>
      </ul>
    `
  }
}

// Function to hash email for gravatar (using pre-computed MD5)
function hashEmail(email: string): string {
  const emailLower = email.toLowerCase().trim()
  // Pre-computed MD5 hashes
  if (emailLower === 'romeo.woutisseth@gmail.com') {
    return 'd940f4d1230378315da17f7d3fd5ed8f'
  }
  if (emailLower === 'magicbart@gmail.com') {
    return 'c0637274270c1c4cbef8bc424530136b'
  }
  // Fallback for other emails
  return '00000000000000000000000000000000'
}

// Team members
const teamMembers = [
  {
    name: 'Roméo Woutisseth',
    role: 'Président',
    description: 'Passionné de jeux vidéo et leader de la communauté',
    skills: ['Gaming', 'Leadership', 'Communauté'],
    email: 'romeo.woutisseth@gmail.com',
    type: 'gravatar'
  },
  {
    name: 'David Benard',
    role: 'Développeur',
    description: 'Développe et maintient la plateforme technique',
    skills: ['Développement', 'Vue.js', 'Backend'],
    email: 'magicbart@gmail.com',
    type: 'gravatar'
  }
]


// Key features
const features = [
  {
    title: 'Open Source',
    description: 'Code source ouvert et contributions communautaires',
    icon: Code,
    color: 'text-purple-600'
  },
  {
    title: 'Sécurisé',
    description: 'Protection des données et authentification robuste',
    icon: Shield,
    color: 'text-green-600'
  },
  {
    title: 'Évolutif',
    description: 'Nouvelles fonctionnalités basées sur vos retours',
    icon: TrendingUp,
    color: 'text-orange-600'
  }
]
</script>

<template>
  <div class="container mx-auto max-w-4xl px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-12">
      <div class="flex justify-center mb-6">
        <div class="bg-gradient-to-r from-primary/20 to-purple-500/20 rounded-full p-4">
          <Heart class="h-10 w-10 text-primary" />
        </div>
      </div>
      <h1 class="text-4xl font-bold mb-4">À Propos de Nous</h1>
      <p class="text-xl text-muted-foreground leading-relaxed max-w-2xl mx-auto">
        Découvrez l'histoire, la mission et l'équipe derrière cette plateforme
        qui réunit une communauté passionnée de technologie et d'innovation.
      </p>
    </div>

    <!-- Mission, Story, Values -->
    <div class="space-y-8 mb-12">
      <div
          v-for="(section, key) in aboutData"
          :key="key"
      >
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-3">
              <div :class="[section.bgColor, 'p-3 rounded-lg']">
                <component :is="section.icon" :class="[section.color, 'h-6 w-6']" />
              </div>
              {{ section.title }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div
                class="prose dark:prose-invert max-w-none"
                v-html="section.content"
            />
          </CardContent>
        </Card>
      </div>
    </div>


    <!-- Team -->
    <Card class="mb-12">
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Users class="h-5 w-5 text-primary" />
          Notre Équipe
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid md:grid-cols-3 gap-6">
          <div
              v-for="member in teamMembers"
              :key="member.name"
              class="text-center p-4 rounded-lg hover:bg-muted/50 transition-colors"
          >
            <div class="mb-3">
              <img 
                :src="`https://www.gravatar.com/avatar/${hashEmail(member.email!)}?s=80&d=identicon`"
                :alt="member.name"
                class="w-16 h-16 rounded-full mx-auto"
              />
            </div>
            <h3 class="font-semibold mb-1">{{ member.name }}</h3>
            <p class="text-primary text-sm font-medium mb-2">{{ member.role }}</p>
            <p class="text-muted-foreground text-sm mb-3">{{ member.description }}</p>
            <div class="flex flex-wrap justify-center gap-1">
              <Badge
                  v-for="skill in member.skills"
                  :key="skill"
                  variant="outline"
                  class="text-xs"
              >
                {{ skill }}
              </Badge>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Features -->
    <Card class="mb-12">
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Award class="h-5 w-5 text-primary" />
          Ce qui nous rend uniques
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid md:grid-cols-2 gap-6">
          <div
              v-for="feature in features"
              :key="feature.title"
              class="flex items-start gap-4 p-4 rounded-lg border hover:bg-muted/50 transition-colors"
          >
            <div class="bg-muted rounded-lg p-2 flex-shrink-0">
              <component :is="feature.icon" :class="[feature.color, 'h-5 w-5']" />
            </div>
            <div>
              <h4 class="font-semibold mb-1">{{ feature.title }}</h4>
              <p class="text-sm text-muted-foreground">{{ feature.description }}</p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Call to Action -->
    <Card>
      <CardContent class="pt-6">
        <div class="text-center">
          <Users class="h-12 w-12 text-primary mx-auto mb-4" />
          <h3 class="text-2xl font-bold mb-4">Rejoignez notre communauté</h3>
          <p class="text-muted-foreground mb-6 max-w-2xl mx-auto">
            Que vous soyez développeur, gamer, créateur de contenu ou simplement passionné de technologie,
            il y a une place pour vous dans notre communauté. Partagez vos connaissances,
            apprenez de nouvelles choses et connectez-vous avec des personnes qui partagent vos passions.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <RouterLink
                :to="{name: 'Register'}"
                class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 transition-colors"
            >
              Créer un compte
            </RouterLink>
            <RouterLink
                :to="{name: 'Contact'}"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background px-6 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors"
            >
              Nous contacter
            </RouterLink>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<style scoped>
.prose :deep(ul) {
  list-style-type: disc;
  list-style-position: inside;
  margin-left: 1rem;
}

.prose :deep(li) {
  margin-bottom: 0.5rem;
}

.prose :deep(p) {
  margin-bottom: 0.75rem;
  line-height: 1.6;
}

.prose :deep(strong) {
  font-weight: 600;
  color: hsl(var(--foreground));
}
</style>
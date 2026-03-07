<!-- src/views/Legal.vue -->
<script setup lang="ts">
import { Scale, Building, Phone, Mail, Globe, Calendar, Shield, AlertTriangle } from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle
} from '@/shared/components/ui/card'
import { Badge } from '@/shared/components/ui/badge'

// Legal information data for French
const legalData = {
  company: {
    title: 'Informations sur l\'éditeur',
    icon: Building,
    color: 'text-blue-600',
    bgColor: 'bg-blue-50',
    sections: [
      {
        id: 'company-info',
        title: 'Association éditrice',
        content: `
          <div class="space-y-3">
            <div>
              <strong>Dénomination sociale :</strong> BAB'INSA<br>
              <strong>Forme juridique :</strong> Association loi 1901<br>
              <strong>Objet social :</strong> Promotion et organisation d'activités de baby-foot à l'INSA de Rouen<br>
              <strong>Déclaration en préfecture :</strong> En cours<br>
              <strong>SIRET :</strong> En cours d'obtention
            </div>
            <div>
              <strong>Siège social :</strong><br>
              INSA Rouen Normandie<br>
              Avenue de l'Université<br>
              76800 Saint-Étienne-du-Rouvray, France
            </div>
            <div>
              <strong>Création :</strong> Octobre 2025<br>
              <strong>Statut :</strong> Association à but non lucratif
            </div>
          </div>
        `
      },
      {
        id: 'management',
        title: 'Bureau et représentation légale',
        content: `
          <div class="space-y-2">
            <div>
              <strong>Président :</strong> Roméo WOUTISSETH<br>
              <strong>Directeur de la publication :</strong> Roméo WOUTISSETH<br>
              <strong>Email :</strong> romeo.woutisseth@gmail.com
            </div>
            <div>
              <strong>Développeur et webmaster :</strong> David BENARD<br>
              <strong>Email technique :</strong> magicbart@gmail.com
            </div>
          </div>
        `
      }
    ]
  },
  contact: {
    title: 'Coordonnées',
    icon: Phone,
    color: 'text-green-600',
    bgColor: 'bg-green-50',
    sections: [
      {
        id: 'contact-info',
        title: 'Nous contacter',
        content: `
          <div class="space-y-3">
            <div>
              <strong>Adresse postale :</strong><br>
              Association BAB'INSA<br>
              INSA Rouen Normandie<br>
              Avenue de l'Université<br>
              76800 Saint-Étienne-du-Rouvray, France
            </div>
            <div>
              <strong>Email du président :</strong> romeo.woutisseth@gmail.com<br>
              <strong>Email technique :</strong> magicbart@gmail.com<br>
              <strong>Site web :</strong> En cours de développement
            </div>
            <div>
              <strong>Disponibilité :</strong><br>
              Pendant les périodes scolaires de l'INSA<br>
              Réponse sous 48h en général
            </div>
          </div>
        `
      }
    ]
  },
  hosting: {
    title: 'Hébergement',
    icon: Globe,
    color: 'text-purple-600',
    bgColor: 'bg-purple-50',
    sections: [
      {
        id: 'hosting-info',
        title: 'Prestataire d\'hébergement',
        content: `
          <div class="space-y-3">
            <div>
              <strong>Hébergeur :</strong> OVH SAS<br>
              <strong>Siège social :</strong> 2 rue Kellermann, 59100 Roubaix, France<br>
              <strong>Téléphone :</strong> 1007 (numéro non surtaxé)
            </div>
            <div>
              <strong>Serveurs :</strong> Union Européenne (RGPD compliant)<br>
              <strong>Localisation des données :</strong> France et Allemagne<br>
              <strong>Sécurité :</strong> Chiffrement SSL/TLS, sauvegardes quotidiennes
            </div>
          </div>
        `
      },
      {
        id: 'technical-info',
        title: 'Informations techniques',
        content: `
          <div class="space-y-2">
            <div>
              <strong>Technologies utilisées :</strong><br>
              • Frontend: Vue.js 3, TypeScript, Tailwind CSS<br>
              • Backend: Go (Golang)<br>
              • Base de données: PostgreSQL<br>
              • CDN: CloudFlare
            </div>
            <div>
              <strong>Certificats de sécurité :</strong><br>
              • SSL/TLS Let's Encrypt<br>
              • Conformité HTTPS obligatoire<br>
              • Headers de sécurité (HSTS, CSP)
            </div>
          </div>
        `
      }
    ]
  },
  legal: {
    title: 'Aspects juridiques',
    icon: Scale,
    color: 'text-orange-600',
    bgColor: 'bg-orange-50',
    sections: [
      {
        id: 'applicable-law',
        title: 'Droit applicable et juridiction',
        content: `
          <div class="space-y-2">
            <div>
              <strong>Droit applicable :</strong> Droit français<br>
              <strong>Juridiction compétente :</strong> Tribunaux de Paris<br>
              <strong>Langue de référence :</strong> Français
            </div>
            <div>
              <strong>En cas de litige :</strong><br>
              Tout litige relatif à l'utilisation de ce site sera soumis
              à la compétence exclusive des tribunaux de Paris,
              nonobstant pluralité de défendeurs ou appel en garantie.
            </div>
          </div>
        `
      },
      {
        id: 'intellectual-property',
        title: 'Propriété intellectuelle',
        content: `
          <div class="space-y-2">
            <div>
              <strong>Droits d'auteur :</strong><br>
              L'ensemble du contenu de ce site (textes, images, logos, etc.)
              est protégé par le droit d'auteur et appartient à l'association BAB'INSA
              ou à ses membres contributeurs.
            </div>
            <div>
              <strong>Utilisation autorisée :</strong><br>
              La consultation et l'utilisation du site par les membres de la communauté
              INSA de Rouen sont librement autorisées. Toute utilisation commerciale
              ou reproduction à des fins autres que celles de l'association
              est strictement interdite sans autorisation écrite préalable.
            </div>
          </div>
        `
      }
    ]
  },
  privacy: {
    title: 'Protection des données',
    icon: Shield,
    color: 'text-red-600',
    bgColor: 'bg-red-50',
    sections: [
      {
        id: 'data-protection',
        title: 'Conformité RGPD',
        content: `
          <div class="space-y-2">
            <div>
              <strong>Responsable de traitement :</strong> Association BAB'INSA<br>
              <strong>Représentant légal :</strong> Roméo WOUTISSETH (Président)<br>
              <strong>Contact données personnelles :</strong><br>
              Email: romeo.woutisseth@gmail.com<br>
              Courrier: BAB'INSA, INSA Rouen, 76800 Saint-Étienne-du-Rouvray
            </div>
            <div>
              <strong>Finalités du traitement :</strong><br>
              • Gestion des comptes des membres<br>
              • Classement ELO et historique des matchs<br>
              • Organisation des tournois<br>
              • Communication associative<br>
              • Fonctionnement de la plateforme
            </div>
          </div>
        `
      },
      {
        id: 'user-rights',
        title: 'Vos droits',
        content: `
          <div class="space-y-2">
            <div>
              Conformément au RGPD, vous disposez des droits suivants :
            </div>
            <div>
              • <strong>Droit d'accès</strong> à vos données personnelles<br>
              • <strong>Droit de rectification</strong> de vos données<br>
              • <strong>Droit à l'effacement</strong> (droit à l'oubli)<br>
              • <strong>Droit à la portabilité</strong> de vos données<br>
              • <strong>Droit d'opposition</strong> au traitement<br>
              • <strong>Droit à la limitation</strong> du traitement
            </div>
            <div>
              <strong>Pour exercer vos droits :</strong> dpo@example.com
            </div>
          </div>
        `
      }
    ]
  }
}

const currentDate = new Date().toLocaleDateString('fr-FR', {
  year: 'numeric',
  month: 'long',
  day: 'numeric'
})
</script>

<template>
  <div class="container mx-auto max-w-4xl px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="flex justify-center mb-4">
        <div class="bg-primary/10 rounded-full p-3">
          <Scale class="h-8 w-8 text-primary" />
        </div>
      </div>
      <h1 class="text-3xl font-bold mb-2">Mentions Légales</h1>
      <p class="text-muted-foreground text-lg">
        Informations légales relatives à ce site internet
      </p>
    </div>

    <!-- Important Notice -->
    <Card class="mb-8 border-blue-200 bg-blue-50">
      <CardContent class="pt-6">
        <div class="flex items-start gap-3">
          <AlertTriangle class="h-6 w-6 text-blue-600 flex-shrink-0 mt-0.5" />
          <div>
            <h3 class="font-semibold text-blue-800 mb-2">Conformité légale</h3>
            <p class="text-blue-700 text-sm">
              Conformément aux dispositions de la loi n° 2004-575 du 21 juin 2004 pour la confiance
              dans l'économie numérique, il est précisé aux utilisateurs du site l'identité des
              différents intervenants dans le cadre de sa réalisation et de son suivi.
            </p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Legal Sections -->
    <div class="space-y-8">
      <div
          v-for="(section, key) in legalData"
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
          <CardContent class="space-y-6">
            <div
                v-for="item in section.sections"
                :key="item.id"
                class="border rounded-lg p-4"
            >
              <h4 class="font-semibold mb-3">{{ item.title }}</h4>
              <div
                  class="prose dark:prose-invert max-w-none text-sm"
                  v-html="item.content"
              />
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Contact Information -->
    <div class="grid md:grid-cols-2 gap-6 mt-8">
      <!-- Quick Contact -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Mail class="h-5 w-5 text-primary" />
            Contact Rapide
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3 text-sm">
            <div>
              <p class="font-semibold mb-2">Questions générales :</p>
              <p class="text-muted-foreground">romeo.woutisseth@gmail.com</p>
            </div>
            <div>
              <p class="font-semibold mb-2">Support technique :</p>
              <p class="text-muted-foreground">magicbart@gmail.com</p>
            </div>
            <div>
              <p class="font-semibold mb-2">Questions juridiques :</p>
              <p class="text-muted-foreground">romeo.woutisseth@gmail.com</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Last Update -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Calendar class="h-5 w-5 text-primary" />
            Dernière mise à jour
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3 text-sm">
            <p>
              <strong>Date :</strong> {{ currentDate }}
            </p>
            <p class="text-muted-foreground">
              Ces mentions légales peuvent être modifiées à tout moment.
              Il est conseillé de les consulter régulièrement.
            </p>
            <div class="pt-2">
              <Badge variant="outline">Version 1.0</Badge>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Footer -->
    <Card class="mt-8">
      <CardContent class="pt-6">
        <div class="text-center">
          <Scale class="h-8 w-8 text-primary mx-auto mb-4" />
          <h3 class="text-lg font-semibold mb-2">Transparence et conformité</h3>
          <p class="text-muted-foreground mb-4">
            Nous nous engageons à respecter la réglementation en vigueur et à maintenir
            ces informations à jour pour garantir la transparence de nos activités.
          </p>
          <div class="flex flex-wrap justify-center gap-2">
            <Badge variant="outline">LCEN Compliant</Badge>
            <Badge variant="outline">RGPD Conforme</Badge>
            <Badge variant="outline">Hébergement EU</Badge>
            <Badge variant="outline">Droit Français</Badge>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<style scoped>
.prose :deep(div) {
  margin-bottom: 0.75rem;
}

.prose :deep(strong) {
  font-weight: 600;
  color: hsl(var(--foreground));
}

.prose :deep(br) {
  margin-bottom: 0.25rem;
}
</style>
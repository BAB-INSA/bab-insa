<!-- src/views/terms/Terms.fr.vue -->
<script setup lang="ts">
import { FileText, Users, Shield, AlertTriangle, Scale, Ban, CheckCircle, Mail } from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle
} from '@/shared/components/ui/card'

// Terms sections data for French
const termsData = {
  acceptance: {
    title: 'Acceptation des Conditions',
    icon: FileText,
    color: 'text-blue-600',
    bgColor: 'bg-blue-50',
    sections: [
      {
        id: 'agreement',
        title: 'Accord contractuel',
        content: `
          <p>En rejoignant l'association BAB'INSA ou en utilisant la plateforme, vous acceptez d'être lié par les présentes Conditions Générales d'Utilisation.</p>
          <p><strong>Si vous n'acceptez pas ces conditions, vous ne pouvez pas utiliser la plateforme ni participer aux activités de l'association.</strong></p>
          <ul>
            <li>Ces CGU constituent un accord entre vous et l'association BAB'INSA</li>
            <li>Votre participation continue constitue une acceptation de toute modification</li>
            <li>L'association se réserve le droit de modifier ces conditions</li>
            <li>Les modifications importantes seront communiquées via la plateforme et/ou par email</li>
          </ul>
        `
      },
      {
        id: 'eligibility',
        title: 'Conditions d\'éligibilité',
        content: `
          <p>Pour rejoindre l'association et utiliser la plateforme, vous devez :</p>
          <ul>
            <li>Être <strong>étudiant ou personnel de l'INSA de Rouen</strong> (ou invité parrainé par un membre)</li>
            <li>Avoir au moins <strong>16 ans</strong> pour créer un compte</li>
            <li>Fournir des informations exactes et vérifiables lors de l'inscription</li>
            <li>Maintenir la sécurité de votre compte et respecter les règles de l'association</li>
            <li>Accepter d'être responsable de toute activité sous votre compte</li>
            <li>Ne pas avoir été précédemment exclu de l'association</li>
          </ul>
        `
      }
    ]
  },
  services: {
    title: 'Description des Services',
    icon: Users,
    color: 'text-green-600',
    bgColor: 'bg-green-50',
    sections: [
      {
        id: 'platform-services',
        title: 'Services proposés',
        content: `
          <p>La plateforme BAB'INSA vous offre :</p>
          <ul>
            <li><strong>Classement ELO :</strong> suivi de votre progression et classement</li>
            <li><strong>Déclaration de matchs :</strong> enregistrement de vos parties de baby-foot</li>
            <li><strong>Profil joueur :</strong> gestion de vos informations et statistiques</li>
            <li><strong>Tournois :</strong> inscription et participation aux événements</li>
            <li><strong>Historique des parties :</strong> consultation de vos matchs passés</li>
            <li><strong>Notifications :</strong> alertes sur les tournois et activités de l'association</li>
          </ul>
          <p>Nous nous efforçons de maintenir la disponibilité du service, mais ne garantissons pas une disponibilité ininterrompue.</p>
        `
      },
      {
        id: 'service-evolution',
        title: 'Évolution du service',
        content: `
          <p>Nous nous réservons le droit de :</p>
          <ul>
            <li>Modifier, suspendre ou interrompre tout ou partie du service</li>
            <li>Ajouter ou supprimer des fonctionnalités</li>
            <li>Effectuer des maintenances programmées ou d'urgence</li>
            <li>Mettre à jour l'interface utilisateur</li>
          </ul>
          <p>Les modifications importantes seront communiquées avec un préavis raisonnable.</p>
        `
      }
    ]
  },
  userObligations: {
    title: 'Obligations des Utilisateurs',
    icon: Shield,
    color: 'text-purple-600',
    bgColor: 'bg-purple-50',
    sections: [
      {
        id: 'prohibited-conduct',
        title: 'Conduites interdites',
        content: `
          <p>Il est strictement interdit de :</p>
          <ul>
            <li><strong>Tricher sur les scores :</strong> falsification des résultats de matchs</li>
            <li><strong>Créer de faux comptes :</strong> usurpation d'identité ou comptes multiples</li>
            <li><strong>Comportement antisportif :</strong> insultes, provocations, mauvaise foi</li>
            <li><strong>Compromettre la sécurité :</strong> tentatives de piratage ou manipulation du système</li>
            <li><strong>Harcèlement :</strong> comportement persistant visant à nuire à un autre membre</li>
            <li><strong>Contourner les sanctions :</strong> création de nouveaux comptes après exclusion</li>
          </ul>
        `
      },
      {
        id: 'data-responsibility',
        title: 'Responsabilité des données',
        content: `
          <p>Vous êtes entièrement responsable des données que vous saisissez :</p>
          <ul>
            <li>Les scores de matchs doivent être exacts et honnêtes</li>
            <li>Vos informations de profil doivent être véridiques</li>
            <li>Vous acceptez que vos données soient vérifiées par les responsables</li>
            <li>Vous renoncez à toute réclamation concernant la correction de données erronées</li>
          </ul>
          <p><strong>L'association se réserve le droit de corriger ou supprimer toute donnée inappropriée sans préavis.</strong></p>
        `
      }
    ]
  },
  intellectualProperty: {
    title: 'Propriété Intellectuelle',
    icon: Scale,
    color: 'text-orange-600',
    bgColor: 'bg-orange-50',
    sections: [
      {
        id: 'our-content',
        title: 'Contenu de l\'association',
        content: `
          <p>Le contenu de la plateforme appartient à l'association BAB'INSA :</p>
          <ul>
            <li><strong>Interface :</strong> design, code, architecture technique de la plateforme</li>
            <li><strong>Nom et logo :</strong> BAB'INSA et éléments visuels identitaires</li>
            <li><strong>Fonctionnalités :</strong> système de classement ELO, algorithmes</li>
            <li><strong>Données de l'association :</strong> historiques, statistiques, classements</li>
          </ul>
          <p>Toute reproduction sans autorisation de l'association est interdite.</p>
        `
      },
      {
        id: 'user-data',
        title: 'Données utilisateur',
        content: `
          <p>Concernant les données que vous saisissez :</p>
          <ul>
            <li>Vos données personnelles vous appartiennent</li>
            <li>Vous autorisez l'association à utiliser vos données sportives (scores, classements)</li>
            <li>Cette autorisation inclut l'affichage public des classements et statistiques</li>
            <li>L'association peut conserver un historique des performances à des fins sportives</li>
            <li>Vous pouvez demander la suppression de vos données personnelles</li>
          </ul>
        `
      }
    ]
  },
  liability: {
    title: 'Responsabilité et Garanties',
    icon: AlertTriangle,
    color: 'text-red-600',
    bgColor: 'bg-red-50',
    sections: [
      {
        id: 'service-availability',
        title: 'Disponibilité du service',
        content: `
          <p>Concernant la disponibilité de nos services :</p>
          <ul>
            <li>Nous nous efforçons de maintenir le service accessible</li>
            <li>Aucune garantie de disponibilité 100% n'est fournie</li>
            <li>Des interruptions peuvent survenir pour maintenance</li>
            <li>Nous ne sommes pas responsables des pannes techniques</li>
            <li>Les cas de force majeure nous dégagent de toute responsabilité</li>
          </ul>
        `
      },
      {
        id: 'limitation-liability',
        title: 'Limitation de responsabilité',
        content: `
          <p>Notre responsabilité est limitée dans les cas suivants :</p>
          <ul>
            <li><strong>Contenu utilisateur :</strong> nous ne sommes pas responsables des commentaires</li>
            <li><strong>Liens externes :</strong> pas de responsabilité pour les sites tiers</li>
            <li><strong>Perte de données :</strong> sauvegardez vos informations importantes</li>
            <li><strong>Dommages indirects :</strong> pas de responsabilité pour les préjudices indirects</li>
          </ul>
          <p>En cas de dommage prouvé, notre responsabilité est limitée au montant des frais payés (service gratuit = 0€).</p>
        `
      }
    ]
  },
  termination: {
    title: 'Résiliation et Sanctions',
    icon: Ban,
    color: 'text-gray-600',
    bgColor: 'bg-gray-50',
    sections: [
      {
        id: 'account-termination',
        title: 'Exclusion de l\'association',
        content: `
          <p>Votre compte peut être supprimé dans les cas suivants :</p>
          <ul>
            <li><strong>À votre demande :</strong> départ volontaire de l'association</li>
            <li><strong>Violation du règlement :</strong> non-respect des règles de l'association</li>
            <li><strong>Comportement antisportif :</strong> triche, harcèlement, violence</li>
            <li><strong>Fin des études :</strong> départ de l'INSA (avec possibilité de maintien selon décision du bureau)</li>
            <li><strong>Inactivité prolongée :</strong> après 2 ans sans connexion</li>
          </ul>
        `
      },
      {
        id: 'consequences',
        title: 'Conséquences de l\'exclusion',
        content: `
          <p>En cas d'exclusion de l'association :</p>
          <ul>
            <li>Vous perdez l'accès à votre profil et à la plateforme</li>
            <li>Vos statistiques de jeu peuvent être conservées pour l'historique de l'association</li>
            <li>Vos données personnelles sont supprimées selon la politique de l'association</li>
            <li>Vous ne pouvez pas participer aux activités de l'association</li>
            <li>Une réintégration nécessite l'accord du bureau de l'association</li>
          </ul>
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
          <FileText class="h-8 w-8 text-primary" />
        </div>
      </div>
      <h1 class="text-3xl font-bold mb-2">Conditions Générales d'Utilisation</h1>
      <p class="text-muted-foreground text-lg">
        Les règles qui régissent l'utilisation de la plateforme BAB'INSA
      </p>
    </div>

    <!-- Important Notice -->
    <Card class="mb-8 border-amber-200 bg-amber-50">
      <CardContent class="pt-6">
        <div class="flex items-start gap-3">
          <AlertTriangle class="h-6 w-6 text-amber-600 flex-shrink-0 mt-0.5" />
          <div>
            <h3 class="font-semibold text-amber-800 mb-2">Contrat légalement contraignant</h3>
            <p class="text-amber-700 text-sm">
              Ces Conditions Générales d'Utilisation constituent un contrat entre vous et l'association BAB'INSA.
              En utilisant la plateforme ou en rejoignant l'association, vous acceptez d'être lié par ces conditions.
              Veuillez les lire attentivement avant de continuer.
            </p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Terms Sections -->
    <div class="space-y-8">
      <div
          v-for="(section, key) in termsData"
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

    <!-- Legal Information -->
    <div class="grid md:grid-cols-2 gap-6 mt-8">
      <!-- Contact Legal -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Mail class="h-5 w-5 text-primary" />
            Contact Juridique
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-sm text-muted-foreground mb-4">
            Pour toute question relative aux présentes CGU :
          </p>
          <div class="space-y-2 text-sm">
            <p><strong>Email :</strong> contact@bab-insa.fr</p>
            <p><strong>Courrier :</strong></p>
            <address class="ml-4 not-italic text-muted-foreground">
              Association BAB'INSA<br>
              INSA Rouen Normandie<br>
              Avenue de l'Université<br>
              76800 Saint-Étienne-du-Rouvray
            </address>
          </div>
        </CardContent>
      </Card>

      <!-- Governing Law -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Scale class="h-5 w-5 text-primary" />
            Droit Applicable
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3 text-sm">
            <div>
              <p class="font-semibold">Loi applicable :</p>
              <p class="text-muted-foreground">Droit français</p>
            </div>
            <div>
              <p class="font-semibold">Juridiction :</p>
              <p class="text-muted-foreground">Tribunaux de Rouen</p>
            </div>
            <div>
              <p class="font-semibold">Langue :</p>
              <p class="text-muted-foreground">Version française fait foi</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Footer -->
    <Card class="mt-8">
      <CardContent class="pt-6">
        <div class="text-center">
          <CheckCircle class="h-8 w-8 text-green-600 mx-auto mb-4" />
          <h3 class="text-lg font-semibold mb-2">Utilisation Responsable</h3>
          <p class="text-muted-foreground mb-4">
            En respectant ces conditions, vous contribuez à maintenir l'esprit sportif et convivial de l'association BAB'INSA.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
            <span class="text-muted-foreground">
              <strong>Version en vigueur depuis le :</strong> {{ currentDate }}
            </span>
            <span class="hidden sm:inline text-muted-foreground">•</span>
            <a
                href="/rules"
                class="text-primary hover:underline font-medium"
            >
              Consulter le règlement communautaire
            </a>
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
}

.prose :deep(strong) {
  font-weight: 600;
  color: hsl(var(--foreground));
}
</style>

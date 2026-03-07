<!-- src/views/FAQ.vue -->
<script setup lang="ts">
import { HelpCircle } from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle
} from '@/shared/components/ui/card'
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger
} from '@/shared/components/ui/accordion'

// FAQ data for French
const faqData = [
  {
    id: 'account',
    title: 'Compte et Authentification',
    questions: [
      {
        id: 'create-account',
        question: 'Comment créer un compte ?',
        answer: `
          <p>Pour rejoindre l'association BAB'INSA et créer un compte :</p>
          <ol>
            <li>Vérifiez que vous êtes étudiant ou personnel de l'INSA de Rouen</li>
            <li>Cliquez sur le bouton "Inscription" en haut à droite</li>
            <li>Remplissez le formulaire avec votre email INSA, nom d'utilisateur et mot de passe</li>
            <li>Confirmez votre mot de passe</li>
            <li>Cliquez sur "Créer un compte"</li>
          </ol>
          <p>Une fois inscrit, vous pourrez déclarer vos matchs et participer aux tournois !</p>
        `
      },
      {
        id: 'forgot-password',
        question: 'J\'ai oublié mon mot de passe, que faire ?',
        answer: `
          <p>Si vous avez oublié votre mot de passe :</p>
          <ol>
            <li>Allez sur la page de connexion</li>
            <li>Cliquez sur "Mot de passe oublié ?"</li>
            <li>Entrez votre adresse email</li>
            <li>Vérifiez votre boîte mail pour le lien de réinitialisation</li>
            <li>Suivez les instructions dans l'email reçu</li>
          </ol>
          <p><strong>Note :</strong> Le lien de réinitialisation expire après 24 heures.</p>
        `
      },
      {
        id: 'change-username',
        question: 'Puis-je changer mon nom d\'utilisateur ?',
        answer: `
          <p>Oui, vous pouvez modifier votre nom d'utilisateur :</p>
          <ol>
            <li>Connectez-vous à votre compte</li>
            <li>Allez dans "Profil" → "Paramètres"</li>
            <li>Dans l'onglet "Informations personnelles", modifiez votre nom d'utilisateur</li>
            <li>Cliquez sur "Enregistrer les modifications"</li>
          </ol>
          <p><strong>Important :</strong> Le nom d'utilisateur doit être unique et contenir entre 3 et 20 caractères.</p>
        `
      }
    ]
  },
  {
    id: 'profile',
    title: 'Profil et Paramètres',
    questions: [
      {
        id: 'edit-profile',
        question: 'Comment modifier mon profil ?',
        answer: `
          <p>Pour modifier votre profil :</p>
          <ol>
            <li>Cliquez sur votre avatar en haut à droite</li>
            <li>Sélectionnez "Profil" dans le menu</li>
            <li>Utilisez les onglets pour modifier différentes sections :
              <ul>
                <li><strong>Informations personnelles :</strong> nom d'utilisateur, email</li>
                <li><strong>Mot de passe :</strong> changez votre mot de passe</li>
              </ul>
            </li>
            <li>Cliquez sur "Enregistrer les modifications" pour chaque section</li>
          </ol>
        `
      },
      {
        id: 'upload-avatar',
        question: 'Comment télécharger une photo de profil ?',
        answer: `
          <p>Nous utilisons Gravatar pour les photos de profil :</p>
          <ol>
            <li>Allez sur <strong>gravatar.com</strong></li>
            <li>Créez un compte avec la même adresse email que votre compte</li>
            <li>Téléchargez votre photo de profil sur Gravatar</li>
            <li>Votre photo apparaîtra automatiquement sur notre plateforme</li>
          </ol>
          <p><strong>Note :</strong> Il peut y avoir un délai de quelques minutes avant que votre nouvelle photo apparaisse.</p>
        `
      }
    ]
  },
  {
    id: 'baby-foot',
    title: 'Baby-foot et Matchs',
    questions: [
      {
        id: 'declare-match',
        question: 'Comment déclarer un match ?',
        answer: `
          <p>Pour déclarer un match de baby-foot :</p>
          <ol>
            <li>Connectez-vous à votre compte</li>
            <li>Allez dans "Nouveau Match" depuis le menu principal</li>
            <li>Sélectionnez votre adversaire (ou vos adversaires en double)</li>
            <li>Indiquez le score final (premier à 10 buts)</li>
            <li>Confirmez la déclaration</li>
          </ol>
          <p><strong>Validation automatique :</strong> Le match est automatiquement validé au bout de 24h dans l'ordre chronologique pour garantir l'équité du système ELO. Pendant ces 24h, votre adversaire peut faire une réclamation s'il conteste le résultat.</p>
        `
      },
      {
        id: 'elo-system',
        question: 'Comment fonctionne le classement ELO ?',
        answer: `
          <p>Le système ELO classe les joueurs selon leurs performances :</p>
          <ul>
            <li><strong>Points de départ :</strong> Tous les nouveaux joueurs commencent à 1200 points</li>
            <li><strong>Gains/Pertes :</strong> Vous gagnez des points en battant des adversaires, en perdez en perdant</li>
            <li><strong>Calcul :</strong> Plus votre adversaire est fort, plus vous gagnez de points en le battant</li>
            <li><strong>Validation :</strong> Les matchs sont validés automatiquement après 24h dans l'ordre chronologique</li>
            <li><strong>Mise à jour :</strong> Le classement est mis à jour quotidiennement avec les matchs validés</li>
          </ul>
          <p>Ce système garantit l'équité en traitant tous les matchs dans l'ordre chronologique, évitant les stratégies de manipulation du classement.</p>
        `
      },
      {
        id: 'match-rules',
        question: 'Quelles sont les règles du baby-foot à l\'association ?',
        answer: `
          <p>Les règles officielles de BAB'INSA :</p>
          <ul>
            <li><strong>Score :</strong> Premier joueur/équipe à 10 buts gagne</li>
            <li><strong>Remise en jeu :</strong> Toujours par le joueur qui a encaissé le but</li>
            <li><strong>Roulette :</strong> Interdite (360° des barres)</li>
            <li><strong>Arrêt de balle :</strong> Maximum 3 secondes par barre</li>
            <li><strong>Sortie de balle :</strong> Remise en jeu par les demis du joueur qui a touché en dernier</li>
          </ul>
          <p><strong>Fair-play :</strong> Le respect des règles et de l'adversaire est primordial !</p>
        `
      },
      {
        id: 'match-dispute',
        question: 'Comment contester un résultat de match ?',
        answer: `
          <p>Si vous contestez un résultat déclaré par votre adversaire :</p>
          <ol>
            <li>Vous avez <strong>24 heures</strong> après la déclaration pour faire une réclamation</li>
            <li>Allez dans "Mes Matchs" et cliquez sur "Contester" à côté du match concerné</li>
            <li>Expliquez brièvement le motif de votre contestation</li>
            <li>Le match est mis en attente et sera examiné par les responsables</li>
            <li>Une décision sera prise et vous serez notifiés tous les deux</li>
          </ol>
          <p><strong>Important :</strong> Passé le délai de 24h, le match est automatiquement validé et ne peut plus être contesté. Les réclamations abusives peuvent être sanctionnées.</p>
        `
      }
    ]
  },
  {
    id: 'tournaments',
    title: 'Tournois et Événements',
    questions: [
      {
        id: 'join-tournament',
        question: 'Comment participer à un tournoi ?',
        answer: `
          <p>Pour participer aux tournois BAB'INSA :</p>
          <ol>
            <li>Consultez la section "Tournois" sur la plateforme</li>
            <li>Choisissez le tournoi qui vous intéresse (simple, double, débutant...)</li>
            <li>Cliquez sur "S'inscrire" avant la date limite</li>
            <li>Vérifiez votre inscription dans votre profil</li>
            <li>Présentez-vous à l'heure et au lieu indiqués</li>
          </ol>
          <p><strong>Note :</strong> Certains tournois ont des critères d'éligibilité (niveau ELO, statut membre, etc.)</p>
        `
      },
      {
        id: 'tournament-format',
        question: 'Quels types de tournois sont organisés ?',
        answer: `
          <p>L'association organise différents types de tournois :</p>
          <ul>
            <li><strong>Tournoi mensuel :</strong> Simple élimination, tous niveaux</li>
            <li><strong>Championnat annuel :</strong> Phase de poules puis élimination directe</li>
            <li><strong>Tournoi débutant :</strong> Réservé aux joueurs ELO < 1100</li>
            <li><strong>Tournoi double :</strong> Par équipes de 2 joueurs</li>
            <li><strong>Events spéciaux :</strong> Journées à thème, inter-associations</li>
          </ul>
          <p>Restez connectés aux annonces pour ne rien rater !</p>
        `
      }
    ]
  },
  {
    id: 'technical',
    title: 'Assistance Technique',
    questions: [
      {
        id: 'browser-support',
        question: 'Quels navigateurs sont supportés ?',
        answer: `
          <p>Notre application est compatible avec :</p>
          <ul>
            <li><strong>Chrome :</strong> version 90 et plus récente</li>
            <li><strong>Firefox :</strong> version 88 et plus récente</li>
            <li><strong>Safari :</strong> version 14 et plus récente</li>
            <li><strong>Edge :</strong> version 90 et plus récente</li>
          </ul>
          <p>Nous recommandons de toujours utiliser la dernière version de votre navigateur pour une expérience optimale.</p>
        `
      },
      {
        id: 'mobile-responsive',
        question: 'L\'application fonctionne-t-elle sur mobile ?',
        answer: `
          <p>Oui, notre application est entièrement responsive et s'adapte à tous les écrans :</p>
          <ul>
            <li>Interface optimisée pour smartphones et tablettes</li>
            <li>Navigation tactile intuitive</li>
            <li>Contenu adaptatif selon la taille d'écran</li>
            <li>Performance optimisée pour les appareils mobiles</li>
          </ul>
        `
      },
      {
        id: 'data-security',
        question: 'Mes données sont-elles sécurisées ?',
        answer: `
          <p>La sécurité de vos données est notre priorité :</p>
          <ul>
            <li><strong>Chiffrement :</strong> Toutes les communications sont chiffrées (HTTPS)</li>
            <li><strong>Authentification :</strong> Tokens JWT sécurisés avec expiration automatique</li>
            <li><strong>Mots de passe :</strong> Hachage sécurisé, jamais stockés en clair</li>
            <li><strong>Sessions :</strong> Gestion automatique des sessions expirées</li>
            <li><strong>Confidentialité :</strong> Vos données ne sont jamais partagées avec des tiers</li>
          </ul>
        `
      }
    ]
  }
]
</script>

<template>
  <div class="container mx-auto max-w-4xl px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="flex justify-center mb-4">
        <div class="bg-primary/10 rounded-full p-3">
          <HelpCircle class="h-8 w-8 text-primary" />
        </div>
      </div>
      <h1 class="text-3xl font-bold mb-2">Foire Aux Questions</h1>
      <p class="text-muted-foreground text-lg">
        Trouvez rapidement les réponses à vos questions les plus fréquentes
      </p>
    </div>

    <!-- FAQ Sections -->
    <div class="space-y-6">
      <div
          v-for="section in faqData"
          :key="section.id"
          class="space-y-4"
      >
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <HelpCircle class="h-5 w-5 text-primary" />
              {{ section.title }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <Accordion type="single" collapsible>
              <AccordionItem
                  v-for="question in section.questions"
                  :key="question.id"
                  :value="question.id"
              >
                <AccordionTrigger class="text-left">
                  {{ question.question }}
                </AccordionTrigger>
                <AccordionContent>
                  <div
                      class="prose dark:prose-invert max-w-none"
                      v-html="question.answer"
                  />
                </AccordionContent>
              </AccordionItem>
            </Accordion>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Contact section -->
    <Card class="mt-8">
      <CardContent class="pt-6">
        <div class="text-center">
          <h3 class="text-lg font-semibold mb-2">Vous ne trouvez pas votre réponse ?</h3>
          <p class="text-muted-foreground mb-4">
            L'équipe de BAB'INSA est là pour vous aider
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a
                href="mailto:contact@bab-insa.fr"
                class="text-primary hover:underline font-medium"
            >
              contact@bab-insa.fr
            </a>
            <span class="hidden sm:inline text-muted-foreground">•</span>
            <a
                href="mailto:support@bab-insa.fr"
                class="text-primary hover:underline font-medium"
            >
              support@bab-insa.fr
            </a>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<style scoped>
.prose :deep(ol) {
  list-style-type: decimal;
  list-style-position: inside;
}

.prose :deep(ul) {
  list-style-type: disc;
  list-style-position: inside;
}

.prose :deep(li) {
  margin-left: 1rem;
  margin-bottom: 0.25rem;
}

.prose :deep(p) {
  margin-bottom: 0.75rem;
}

.prose :deep(strong) {
  font-weight: 600;
  color: hsl(var(--foreground));
}

.prose :deep(ol li),
.prose :deep(ul li) {
  margin-bottom: 0.25rem;
}

.prose :deep(ul ul),
.prose :deep(ol ol) {
  margin-top: 0.25rem;
  margin-left: 1rem;
}
</style>
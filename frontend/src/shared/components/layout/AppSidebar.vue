<script setup lang="ts">
import { Home, Users, HelpCircle, Shield, Eye, FileText, Mail, Heart, Scale, Settings, Trophy, History, Users2, Swords } from "lucide-vue-next"
import {
    Sidebar,
    SidebarContent,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/shared/components/ui/sidebar"
import { useSidebar } from '@/shared/components/ui/sidebar'
import { useRouter } from 'vue-router'
import { watch } from 'vue'
import { useAdmin } from '@/features/admin/composables/useAdmin'

const { setOpenMobile } = useSidebar()
const router = useRouter()
const { isAdmin } = useAdmin()

/**
 * Watch for route changes and close mobile sidebar
 * This handles cases where navigation happens programmatically
 */
watch(() => router.currentRoute.value.path, () => {
    setOpenMobile(false)
})
</script>

<template>
  <Sidebar>
    <SidebarContent>
      <SidebarGroup>
        <SidebarGroupLabel>Application</SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'Home'}">
                  <Home />
                  <span>Accueil</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'PlayerLeaderboard'}">
                  <Trophy />
                  <span>Classement</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'PlayerList'}">
                  <Users />
                  <span>Liste des Joueurs</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>

            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'TeamList'}">
                  <Users />
                  <span>Liste des Équipes</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>

            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'MatchesHistory'}">
                  <History />
                  <span>Historique des Matchs 1v1</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>

            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'TeamMatchesHistory'}">
                  <Users2 />
                  <span>Historique des Matchs 2v2</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>

            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'TournamentList'}">
                  <Swords />
                  <span>Tournois</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>

      <!-- Section Admin -->
      <SidebarGroup v-if="isAdmin" class="mt-6">
        <SidebarGroupLabel>Administration</SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'AdminUsers'}">
                  <Settings />
                  <span>Gestion des utilisateurs</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'AdminMatches'}">
                  <Trophy />
                  <span>Gestion des matchs 1v1</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'AdminTeamMatches'}">
                  <Users />
                  <span>Gestion des matchs 2v2</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'AdminTournaments'}">
                  <Swords />
                  <span>Gestion des tournois</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>

      <!-- Section Liens utiles -->
      <SidebarGroup class="mt-6">
        <SidebarGroupLabel>Liens utiles</SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'Faq'}">
                  <HelpCircle />
                  <span>FAQ</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'Rules'}">
                  <Shield />
                  <span>Rules</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'Privacy'}">
                  <Eye />
                  <span>Privacy</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'Terms'}">
                  <FileText />
                  <span>Terms</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'Contact'}">
                  <Mail />
                  <span>Contact</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'About'}">
                  <Heart />
                  <span>About</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton asChild>
                <RouterLink :to="{name: 'Legal'}">
                  <Scale />
                  <span>Legal</span>
                </RouterLink>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>
    </SidebarContent>
  </Sidebar>
</template>

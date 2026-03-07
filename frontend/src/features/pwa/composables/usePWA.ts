// src/composables/usePWA.ts
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue'
import pwaService from '@/features/pwa/services/pwa.service.ts'
import toastService from '@/shared/services/toast.service.ts'

/**
 * PWA Install Event interface
 */
interface PWAInstallEvent extends Event {
    prompt(): Promise<{ outcome: 'accepted' | 'dismissed' }>
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>
}

/**
 * PWA Composable for managing Progressive Web App functionality in Vue components
 */
export function usePWA() {
    // Reactive state
    const state = reactive({
        updateAvailable: false,
        offlineReady: false,
        needRefresh: false,
        isPWA: false,
        canInstall: false,
        isOnline: navigator.onLine,
        installing: false,
        installPrompt: null as PWAInstallEvent | null
    })

    // Storage estimate
    const storageEstimate = ref<StorageEstimate | null>(null)

    /**
     * Enhanced detection of PWA installation capability
     */
    const enhancedCanInstall = computed(() => {
        // Check if we have an install prompt stored
        if (state.installPrompt) return true

        // Check if beforeinstallprompt is supported
        if ('beforeinstallprompt' in window) return true

        // Check if we're in a context that supports installation
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        const hasNavigatorStandalone = 'standalone' in window.navigator

        // If already installed, can't install again
        if (state.isPWA || isStandalone) return false

        // Check for browser support
        const supportsServiceWorker = 'serviceWorker' in navigator
        const supportsManifest = 'onbeforeinstallprompt' in window

        return supportsServiceWorker && (supportsManifest || hasNavigatorStandalone)
    })

    /**
     * Enhanced PWA detection
     */
    const enhancedIsPWA = computed(() => {
        // Check display mode
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches

        // Check iOS standalone
        const isIOSStandalone = (window.navigator as Navigator & { standalone?: boolean }).standalone === true

        // Check if launched from home screen (Android)
        const isAndroidStandalone = document.referrer.includes('android-app://')

        return isStandalone || isIOSStandalone || isAndroidStandalone
    })

    /**
     * Update the service worker
     */
    const updateApp = async () => {
        try {
            await pwaService.updateServiceWorker()
        } catch (error) {
            console.error('Failed to update app:', error)
            toastService.error('Échec de la mise à jour', 'Impossible de mettre à jour l\'application')
        }
    }

    /**
     * Install the PWA using the PWA service
     */
    const installApp = async () => {
        if (!state.canInstall) {
            console.warn('Cannot install: no install capability detected')
            return
        }

        state.installing = true

        try {
            const result = await pwaService.triggerInstall()

            if (result?.outcome === 'accepted') {
                console.log('PWA installation accepted')
                toastService.success('App installée', 'L\'application a été installée avec succès')
                state.installPrompt = null
                state.canInstall = false
                state.isPWA = true
            } else if (result?.outcome === 'dismissed') {
                console.log('PWA installation declined')
            } else {
                console.warn('Install failed: no install prompt available')
                toastService.warning('Installation non disponible', 'L\'installation de l\'app n\'est pas disponible sur cet appareil')
            }
        } catch (error) {
            console.error('PWA installation failed:', error)
            toastService.error('Échec de l\'installation', 'Impossible d\'installer l\'application')
        } finally {
            state.installing = false
        }
    }

    /**
     * Clear all application caches
     */
    const clearCaches = async () => {
        try {
            await pwaService.clearCaches()
            toastService.success('Caches vidés', 'Tous les caches de l\'application ont été vidés')
        } catch (error) {
            console.error('Failed to clear caches:', error)
            toastService.error('Échec du vidage', 'Impossible de vider les caches de l\'application')
        }
    }

    /**
     * Get storage usage information
     */
    const getStorageInfo = async () => {
        try {
            storageEstimate.value = await pwaService.getStorageEstimate()
        } catch (error) {
            console.error('Failed to get storage info:', error)
        }
    }

    /**
     * Format bytes to human readable string
     */
    const formatBytes = (bytes: number | undefined): string => {
        if (!bytes) return '0 B'

        const sizes = ['B', 'KB', 'MB', 'GB']
        const i = Math.floor(Math.log(bytes) / Math.log(1024))
        return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${sizes[i]}`
    }

    /**
     * Calculate storage usage percentage
     */
    const getStorageUsagePercentage = (): number => {
        if (!storageEstimate.value?.quota || !storageEstimate.value?.usage) return 0
        return Math.round((storageEstimate.value.usage / storageEstimate.value.quota) * 100)
    }

    /**
     * Handle online/offline events
     */
    const handleOnlineChange = () => {
        const wasOffline = !state.isOnline
        state.isOnline = navigator.onLine

        if (state.isOnline && wasOffline) {
            toastService.success('De retour en ligne', 'Connexion Internet rétablie')
        } else if (!state.isOnline) {
            toastService.warning('Mode hors ligne', 'Vous êtes maintenant hors ligne. Certaines fonctionnalités peuvent être limitées.')
        }
    }

    /**
     * Handle PWA events
     */
    const handlePWAEvents = (event: Event) => {
        switch (event.type) {
            case 'pwa:offline-ready':
                state.offlineReady = true
                toastService.success(
                    'App prête',
                    'L\'application est prête à fonctionner hors ligne',
                    { duration: 5000 }
                )
                break

            case 'pwa:update-available':
                state.updateAvailable = true
                state.needRefresh = true
                toastService.info(
                    'Mise à jour disponible',
                    'A new version is available. Click to update.',
                    {
                        duration: 0, // Don't auto-close
                        autoClose: false
                    }
                )
                break

            case 'pwa:update-success':
                toastService.success(
                    'Mise à jour terminée',
                    'L\'application a été mise à jour vers la dernière version'
                )
                break
        }
    }

    /**
     * Handle install prompt ready event from PWA service
     */
    const handleInstallPromptReady = (event: CustomEvent) => {
        console.log('Install prompt ready event received', event.detail)
        state.canInstall = true
        state.installPrompt = pwaService.getInstallPrompt()
        console.log('PWA install prompt ready')
    }

    /**
     * Handle app installed event from PWA service
     */
    const handleAppInstalled = () => {
        console.log('PWA was installed')
        state.installPrompt = null
        state.canInstall = false
        state.isPWA = true

        toastService.success('App installée', 'L\'application a été installée avec succès')
    }

    /**
     * Update state from PWA service
     */
    const updateState = () => {
        const status = pwaService.getStatus()

        // Update basic status
        state.updateAvailable = status.updateAvailable
        state.offlineReady = status.offlineReady
        state.needRefresh = status.needRefresh

        // Use enhanced detection for PWA and install capability
        state.isPWA = enhancedIsPWA.value
        state.canInstall = enhancedCanInstall.value
        state.isOnline = status.isOnline
    }

    /**
     * Check for delayed install prompt
     */
    const checkForInstallPrompt = () => {
        // Sometimes the beforeinstallprompt event is delayed
        // Check for it periodically during the first few seconds
        setTimeout(() => {
            if (!state.installPrompt && !state.isPWA) {
                // Force check for install capabilities
                state.canInstall = enhancedCanInstall.value
                console.log('Delayed install check:', state.canInstall)
            }
        }, 2000)
    }

    // Lifecycle hooks
    onMounted(async () => {
        console.log('PWA composable mounted')

        // Initialize PWA service
        await pwaService.init()

        // Update initial state
        updateState()

        // Get initial storage info
        await getStorageInfo()

        // Add event listeners
        window.addEventListener('online', handleOnlineChange)
        window.addEventListener('offline', handleOnlineChange)

        // Listen to PWA service events instead of direct browser events
        window.addEventListener('pwa:install-prompt-ready', handleInstallPromptReady as EventListener)
        window.addEventListener('pwa:app-installed', handleAppInstalled as EventListener)

        // PWA custom events
        window.addEventListener('pwa:offline-ready', handlePWAEvents)
        window.addEventListener('pwa:update-available', handlePWAEvents)
        window.addEventListener('pwa:update-success', handlePWAEvents)

        // Check for delayed install prompt
        checkForInstallPrompt()

        console.log('PWA state after mount:', {
            isPWA: state.isPWA,
            canInstall: state.canInstall,
            hasBeforeInstallPrompt: 'beforeinstallprompt' in window,
            isStandalone: window.matchMedia('(display-mode: standalone)').matches
        })
    })

    onUnmounted(() => {
        // Clean up event listeners
        window.removeEventListener('online', handleOnlineChange)
        window.removeEventListener('offline', handleOnlineChange)
        window.removeEventListener('pwa:install-prompt-ready', handleInstallPromptReady as EventListener)
        window.removeEventListener('pwa:app-installed', handleAppInstalled as EventListener)

        // PWA custom events
        window.removeEventListener('pwa:offline-ready', handlePWAEvents)
        window.removeEventListener('pwa:update-available', handlePWAEvents)
        window.removeEventListener('pwa:update-success', handlePWAEvents)
    })

    return {
        // State - use enhanced computed values where applicable
        updateAvailable: computed(() => state.updateAvailable),
        offlineReady: computed(() => state.offlineReady),
        needRefresh: computed(() => state.needRefresh),
        isPWA: enhancedIsPWA,
        canInstall: enhancedCanInstall,
        isOnline: computed(() => state.isOnline),
        installing: computed(() => state.installing),
        installPrompt: computed(() => state.installPrompt),
        storageEstimate,

        // Actions
        updateApp,
        installApp,
        clearCaches,
        getStorageInfo,

        // Computed
        formatBytes,
        getStorageUsagePercentage,

        // Getters
        storageUsed: () => formatBytes(storageEstimate.value?.usage),
        storageQuota: () => formatBytes(storageEstimate.value?.quota),
        storageUsagePercent: getStorageUsagePercentage
    }
}

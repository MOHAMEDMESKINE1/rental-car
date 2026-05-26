import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import { Modal, ModalLink, renderApp } from '@inertiaui/modal-vue'
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
import { putConfig } from '@inertiaui/modal-vue'
import { withInertiaModal } from '@inertiaui/modal-vue'

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
            case name.startsWith('teams/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    withApp(app) {
        withInertiaModal(app)
        app.component('Modal', Modal)
        app.component('ModalLink', ModalLink)
    },
    progress: {
        color: '#4B5563',
    },

});
putConfig({
   modal: {

        closeExplicitly: true,
    },
    slideover: {
        closeExplicitly: true,
    },
})

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();

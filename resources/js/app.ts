import { createInertiaApp } from '@inertiajs/vue3';
import type { DefineComponent } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import Dashboard from '@/pages/Dashboard.vue';
import LeadsIndex from '@/pages/Leads/Index.vue';
import Statistics from '@/pages/Statistics.vue';
import LeadsShow from '@/pages/Leads/Show.vue';
import Branches from '@/pages/Branches/Index.vue';
import Accounts from '@/pages/Accounts/Index.vue';
import Login from '@/pages/auth/Login.vue';

const appName = import.meta.env.VITE_APP_NAME || 'KGJ Leads';
const pages = {
    Dashboard,
    'Leads/Index': LeadsIndex,
    'Leads/Show': LeadsShow,
    Statistics,
    'Branches/Index': Branches,
    'Accounts/Index': Accounts,
    'auth/Login': Login,
};

void createInertiaApp({
    resolve: (name) => {
        const page = pages[name as keyof typeof pages];
        if (!page) {
            throw new Error(`Halaman ${name} tidak tersedia.`);
        }

        return page as unknown as DefineComponent;
    },
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name === 'auth/Login':
                return null;
            default:
                return AppLayout;
        }
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
});

initializeFlashToast();

import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
    faFacebook,
    faInstagram,
    faTwitter,
    faLinkedin,
    faYoutube,
    faGithub,
    faTiktok,
    faWhatsapp,
    faTelegram,
    faPinterest,
    faSnapchat,
    faThreads,
    faDribbble,
    faBehance,
    faSpotify,
    faSoundcloud,
    faTwitch,
    faDiscord,
    faApple,
} from '@fortawesome/free-brands-svg-icons';
import { faGlobe, faEnvelope, faPhone, faLink } from '@fortawesome/free-solid-svg-icons';

// Add icons to library
library.add(
    faFacebook,
    faInstagram,
    faTwitter,
    faLinkedin,
    faYoutube,
    faGithub,
    faTiktok,
    faWhatsapp,
    faTelegram,
    faPinterest,
    faSnapchat,
    faThreads,
    faDribbble,
    faBehance,
    faSpotify,
    faSoundcloud,
    faTwitch,
    faDiscord,
    faApple,
    faGlobe,
    faEnvelope,
    faPhone,
    faLink
);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        app.component('font-awesome-icon', FontAwesomeIcon);
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

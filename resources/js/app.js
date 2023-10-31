import './bootstrap'
import '../css/app.css'
import "leaflet/dist/leaflet.css";
import "video.js/dist/video-js.css";

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import vuetify from './Plugins/vuetify'
import toast from './Plugins/toast'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(vuetify)
      .use(toast)
      .mount(el)
  },
  progress: {
    color: '#4CAF50',
  },
})

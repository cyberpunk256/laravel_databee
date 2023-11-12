import './bootstrap'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import vuetify from './Plugins/vuetify'
import toast from './Plugins/toast'
import mixin from './Plugins/mixin'
import moment from './Plugins/moment'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel'
window.moment = moment

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(vuetify)
      .use(toast)
      .mixin(mixin)
      .mount(el)
  },
  progress: {
    color: '#4CAF50',
  },
})

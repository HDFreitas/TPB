import { createApp } from 'vue';
import { createPinia } from 'pinia';

// Plugins
import router from '@/router/index';
import vuetify from '@/plugins/vuetify';
import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';
import VueTablerIcons from 'vue-tabler-icons';
// import VueApexCharts from "vue3-apexcharts";

import '@/styles/style.scss';
import 'vue3-perfect-scrollbar/style.css';

import App from './App.vue';

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.use(vuetify)
// app.use(VueApexCharts, {})
app.use(VueTablerIcons)
app.use(PerfectScrollbarPlugin)
app.mount('#app')


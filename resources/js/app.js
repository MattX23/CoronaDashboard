require('./bootstrap');

import Vue from 'vue'
import App from './components/App.vue'

Vue.component('app', require('./components/App.vue').default);
Vue.component('nav-bar', require('./components/Nav.vue').default);
Vue.component('home-view', require('./components/HomeView.vue').default);
Vue.component('bar-chart', require('./components/BarChart.vue').default);

const app = new Vue({
    el: '#app',
    components: { App }
});
require('./bootstrap')

import axios from 'axios'

import Vue from 'vue'
import VueAxios from 'vue-axios'
import VueRouter from 'vue-router'

import router from './Router/index'
import App from './App.vue'
import store from './Store/store'

Vue.use(VueRouter)
Vue.use(VueAxios, axios)

const app = new Vue({
	el: '#app',
	router,
    store,
	render: h => h(App)
})

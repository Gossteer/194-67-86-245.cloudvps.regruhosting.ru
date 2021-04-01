/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import Vue from 'vue';
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import {router, constants} from './helpers';
import {store} from './store';
import connect from '@vkontakte/vk-connect';

// Sends event to client
connect.send("VKWebAppInit", {});
// Subscribes to event, sended by client
connect.subscribe(e => console.log(e));
window.connect = connect;

Vue.use(ElementUI);

import Main from './components/Main';

window.constants = constants;

window.Vue = Vue;

const index = new Vue({
    router,
    store,
    render: (h) => h(Main)
}).$mount('#app');

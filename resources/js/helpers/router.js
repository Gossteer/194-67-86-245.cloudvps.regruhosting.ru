import Vue from 'vue';
import VueRouter from 'vue-router';
import {store} from '../store';

Vue.use(VueRouter);

const Api = () => import(/* webpackChunkName: 'api' */ '../components/api/View.vue');
const EditApi = () => import(/* webpackChunkName: 'edit-api' */ '../components/api/Edit.vue');
const Search = () => import(/* webpackChunkName: 'search' */ '../components/search/View.vue');
const EditSearch = () => import(/* webpackChunkName: 'edit-search' */ '../components/search/Edit.vue');
const Subscriptions = () => import(/* webpackChunkName: 'subscriptions' */ '../components/subscriptions/Index.vue');
const Friends = () => import(/* webpackChunkName: 'friends' */ '../components/friends/Index.vue');
const Tags = () => import(/* webpackChunkName: 'tags' */ '../components/tags/Index.vue');
const Admin = () => import(/* webpackChunkName: 'admin' */ '../components/admin/Index.vue');

const routes = [
    {
        path: '/edit-api',
        name: 'edit-api',
        component: EditApi,
        props: true
    },
    {
        path: '/api',
        name: 'api',
        component: Api,
        props: true
    },
    {
        path: '/edit-search',
        name: 'edit-search',
        component: EditSearch,
        props: true
    },
    {
        path: '/search',
        name: 'search',
        component: Search,
        props: true
    },
    {
        path: '/subscriptions',
        name: 'subscriptions',
        component: Subscriptions,
        props: true
    },
    {
        path: '/friends',
        name: 'friends',
        component: Friends,
    },
    {
        path: '/',
        name: 'tags',
        component: Tags,
        props: true
    },
    {
        path: '/admin',
        name: 'admin',
        component: Admin
    }
];

export const router = new VueRouter({
    mode: 'history',
    routes: routes,
});

router.beforeEach(async (to, from, next) => {
    store.dispatch('main/setLoadingFlag', true);
    if (!to.meta.middleware) {
        return next()
    }
    const middleware = to.meta.middleware;
    const context = {
        to,
        from,
        next,
        store
    };

    return middleware[0]({
        ...context,
        nextMiddleware: middlewarePipeline(context, middleware, 1)
    })
});

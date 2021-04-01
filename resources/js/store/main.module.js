import {requestVkApiGet, requestVkApiPost} from "../helpers/request";

let state = {
    user: {},
    groups: {},
    tags: {},
    partnerMarker: PARTNER_TEXT,
    loading: false,
    currencies: {
        'RUB': 'рубль',
        'BYN': 'беларусский рубль',
        'EUR': 'евро',
        'USD': 'доллар',
        'CNY': 'юань',
        'UAH': 'гривна',
        'KZT': 'тенге ',
        'KGS': 'сом',
        'UZS': 'сум',
        'AZN': 'манат',
        'THB': 'бат',
    },
    passengers: {
        'url': '1 пассажир, экономкласс',
        'url2Passengers': '2 пассажира, экономкласс',
        'url1Business': '1 пассажир, бизнес-класс',
        'urlForma': 'открытая редактируемая форма поиска'
    },
    languages: {
        "ru": "русский",
        "en": "английский",
        "fr": "французский",
        "de": "немецкий",
        "it": "итальянский",
        "zh-CN": "китайский",
        "th": "тайский",
        "tr": "турецкий",
        "es": "испанский",
    }
};

const actions = {
    async getGroupsFunc({getters, dispatch, commit}, {}) {
        const groups = await fetch('https://api.cheapflights.sale/api/Channels', {
            method: 'GET'
        });
        commit('groups', await groups.json());
    },
    async getGroupIdFunc({getters, dispatch, commit}, {params, accessToken = null}) {
        try {
            let groupId = await requestVkApiGet(
                'groups.getById',
                params,
                accessToken
            );
            return groupId.response ? groupId.response[0] : null;
        } catch (e) {
            console.log('err ', err);
        }
    },
    async getCheckboxes({getters, dispatch, commit}, userId) {
        try {
            let checkboxes = {};
            var requests = await fetch('/get-tags', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({user_id: userId})
            });
            var requestsJson = await requests.json();
            requestsJson.tags.forEach((data) => {
                checkboxes[data.body + '/' + data.group_id + '/' + data.group_name + '/' + data.category + '/' + data.section] = true;
            });
            commit('tags', checkboxes);
        } catch (e) {
            console.log('err ', e);
            commit('tags', {});
        }
    },
    setLoadingFlag({getters, dispatch, commit}, flag) {
        commit('loading', flag);
    },
    setPartnerMarker({getters, dispatch, commit}, partnerMarker) {
        commit('partnerMarker', partnerMarker);
    },
    async getUserInfo({getters, dispatch, commit}) {
        let user = await window.connect.send("VKWebAppGetUserInfo", {});
        try {
            commit('user', user);
        } catch (e) {
            console.log('err ', e);
        }
    },
};
const mutations = {
    user(state, user) {
        state.user = user;
    },
    tags(state, tags) {
        state.tags = tags;
    },
    groups(state, groups) {
        state.groups = groups;
    },
    loading(state, flag) {
        state.loading = flag;
    },
    partnerMarker(state, partnerMarker) {
        state.partnerMarker = partnerMarker;
    },
};
const getters = {
};

export const main = {
    namespaced: true,
    state,
    actions,
    mutations,
    getters
};

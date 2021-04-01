<template>
    <el-row style="width: 100%;">
        <el-row>
            <el-col class="aviasales-tab"
                    v-bind:class="{ showSearch: tabs[0] }"
                    id="friendSearchForm" :xs="24" :sm="24" :md="24" :lg="24" :xl="24"
                    style="margin-top: 0;padding: 0 20px;">
            </el-col>
            <el-col class="aviasales-tab"
                    v-bind:class="{ showSearch: tabs[1] }"
                    id="friendCalendar" :xs="24" :sm="24" :md="24" :lg="24" :xl="24"
                    style="margin-top: 0;padding: 0 20px;">
            </el-col>
            <el-col class="aviasales-tab"
                    v-bind:class="{ showSearch: tabs[2] }"
                    id="friendSchedule" :xs="24" :sm="24" :md="24" :lg="24" :xl="24"
                    style="margin-top: 0;padding: 0 20px;">
            </el-col>
            <el-col class="aviasales-tab"
                    v-bind:class="{ showSearch: tabs[3] }"
                    id="friendPopularDirections" :xs="24" :sm="24" :md="24" :lg="24" :xl="24"
                    style="margin-top: 0;padding: 0 20px;">
            </el-col>
            <el-col class="aviasales-tab"
                    v-bind:class="{ showSearch: tabs[5] }"
                    id="friendSpecial" :xs="24" :sm="24" :md="24" :lg="24" :xl="24"
                    style="margin-top: 0;padding: 0 20px;">
            </el-col>
            <el-col class="aviasales-tab"
                    v-bind:class="{ showSearch: tabs[6] }"
                    id="friend100Tickets" :xs="24" :sm="24" :md="24" :lg="24" :xl="24"
                    style="margin-top: 0;padding: 0 20px;">
                <iframe
                    :src="'https://top100.aviasales.ru/'+elementUser.code+'?_ga=2.230093269.899426115.1587444996-1554969250.1586643377&utm_campaign=as_marketing_top100&utm_medium=aviasales&utm_source=latest_prices'"
                    height="570px"
                    scrolling="yes"
                    frameborder="0"
                    width="100%"
                    id="friend100TicketsIframe"
                >
                </iframe>
            </el-col>
        </el-row>
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" style="padding: 0 20px;position: fixed;bottom: 20px;">
            <el-cascader
                v-model="value"
                :options="options"
                @change="selectTab"
                popper-class="bottom-search-row-popper"
                style="width: 100%;"
            >
            </el-cascader>
        </el-col>
    </el-row>
</template>

<script>
    import {store} from "../../store";
    import {mapState, mapActions} from 'vuex';
    import {getCityAviasalesInfo} from "../../helpers";

    export default {
        props: {
            isFriend: {
                type: Boolean,
                default: false
            },
            friend: {
                type: Object,
                default: {}
            }
        },
        data() {
            return {
                tabs: [true, true, true, true, true, true, true,],
                siteUrl: process.env.MIX_SITE_URL,
                elementUser: {name: '', code: 'MOW'},
                elementFriend: {name: '', code: ''},
                mapWidth: 100,
                value: [1],
                options: [],
            }
        },
        async mounted() {
            this.value = this.isFriend ? [1] : [0];
            let res = await getCityAviasalesInfo(this.user.city.title);
            if (res) {
                this.elementUser = res;
            }
            if (this.friend) {
                res = await getCityAviasalesInfo(this.friend.city.title);
                if (res) {
                    this.elementFriend = res;
                }
            }

            this.createSearchForm(this.elementUser.name, this.elementFriend.name);
            this.createCalendar(this.elementUser.code, this.elementFriend.code);
            this.createSchedule(this.elementUser.code, this.elementFriend.code);
            this.createPopularDirections(this.elementUser.code, this.elementFriend.code);
            this.createSpecial(this.elementUser.code, this.elementFriend.code);

            this.isFriend ? this.selectTab(1) : this.selectTab(0);
            this.options = [
                {
                    value: 0,
                    label: 'Поиск авиабилетов',
                    /*   disabled: this.tabs[0]*/
                },
                {
                    value: 1,
                    label: 'Календарь низких цен',
                    /*    disabled: this.tabs[1]*/
                },
                {
                    value: 2,
                    label: 'Расписания',
                    /*   disabled: this.tabs[2]*/
                },
                {
                    value: 3,
                    label: 'Популярные направления',
                    /*      disabled: this.tabs[3]*/
                },
                {
                    value: 5,
                    label: 'Спецпредложения',
                    /*    disabled: this.tabs[5]*/
                },
                {
                    value: 6,
                    label: '100 дешевых авиабилетов',
                    /*     disabled: this.tabs[6]*/
                },
            ];
            store.dispatch('main/setLoadingFlag', false);
        },
        computed: {
            ...mapState({
                user: (state) => state.main.user,
                partnerMarker: (state) => state.main.partnerMarker,
            }),
        },
        methods: {
            selectTab(index) {
                this.refreshTab();
                this.tabs[index] = true;
            },
            refreshTab() {
                this.tabs = [false, false, false, false, false, false, false]
            },
            createSpecial(src, dst) {
                let s = document.createElement("script");
                s.type = "text/javascript";
                s.src = "https://www.travelpayouts.com/ducklett/scripts.js?" +
                    "v=1&" +
                    "marker=" + this.partnerMarker + "&" +
                    "widget_type=slider&" +
                    "host=hydra.aviasales.ru&" +
                    "locale=ru&" +
                    "currency=rub&" +
                    "limit=9&" +
                    "powered_by=true&" +
                    "origin_iatas=" + src + "&" +
                    "destination_iatas=" + dst;
                document.querySelector('#friendSpecial').append(s);
            },
            createPopularDirections(src, dst) {
                let s = document.createElement("script");
                s.type = "text/javascript";
                s.src = "https://www.travelpayouts.com/weedle/widget.js?" +
                    "v=1&" +
                    "marker=" + this.partnerMarker + "&" +
                    "host=hydra.aviasales.ru&" +
                    "locale=ru&" +
                    "currency=usd&" +
                    "powered_by=true&" +
                    "destination=" + dst;

                document.querySelector('#friendPopularDirections').append(s);
            },
            createSchedule(src, dst) {
                let s = document.createElement("script");
                s.type = "text/javascript";
                s.src = "https://tp.media/content?" +
                    "origin=" + src + "&" +
                    "destination=" + dst + "&" +
                    "promo_id=2811&" +
                    "shmarker=" + this.partnerMarker + "&" +
                    "campaign_id=100&" +
                    "target_host=hydra.aviasales.ru&" +
                    "locale=ru&" +
                    "powered_by=true&" +
                    "airline=&" +
                    "non_direct_flights=true&" +
                    "min_lines=&" +
                    "border_radius=0&" +
                    "color_background=%23FFFFFF&" +
                    "color_text=%23000000&" +
                    "color_border=%23FFFFFF";

                document.querySelector('#friendSchedule').append(s);
            },
            createCalendar(src, dst) {
                let s = document.createElement("script");
                s.type = "text/javascript";
                s.src = "https://www.travelpayouts.com/calendar_widget/iframe.js?" +
                    "origin=" + src + "&" +
                    "destination=" + dst + "&" +
                    "marker=" + this.partnerMarker + "&" +
                    "searchUrl=hydra.aviasales.ru&" +
                    "locale=ru&" +
                    "currency=rub&" +
                    "powered_by=true&" +
                    "one_way=false&" +
                    "only_direct=false&" +
                    "period=year&" +
                    "range=2%2C4";
                document.querySelector('#friendCalendar').append(s);
            },
            createSearchForm(src, dst) {
                store.dispatch('main/setLoadingFlag', false);
                let s = document.createElement("script");
                s.type = "text/javascript";
                s.src = "https://old.travelpayouts.com/widgets/a92ec352ec5c4a659f1ad07c4bf65d99.js?v=1907";
                document.querySelector('#friendSearchForm').append(s);
                window.TP_FORM_SETTINGS = window.TP_FORM_SETTINGS || {};
                window.TP_FORM_SETTINGS["a92ec352ec5c4a659f1ad07c4bf65d99"] = {
                    "handle": "a92ec352ec5c4a659f1ad07c4bf65d99",
                    "widget_name": "Поисковая форма #2",
                    "border_radius": "0",
                    "additional_marker": "appvk",
                    "width": null,
                    "show_logo": false,
                    "show_hotels": true,
                    "form_type": "avia_hotel",
                    "locale": "ru",
                    "currency": "rub",
                    "sizes": "default",
                    "search_target": "_self",
                    "active_tab": "avia",
                    "search_host": "hydra.aviasales.ru",
                    "hotels_host": "hotellook.ru/search",
                    "hotel": "",
                    "hotel_alt": "Лучшие цены на отели, фото, отзывы, бронирование отелей в любом городе на Hotellook.ru",
                    "avia_alt": "Первый российский метапоиск авиабилетов, сравнение цен, свежие спецпредложения авиакомпаний - aviasales",
                    "retargeting": true,
                    "trip_class": "economy",
                    "depart_date": null,
                    "return_date": null,
                    "check_in_date": null,
                    "check_out_date": null,
                    "no_track": false,
                    "powered_by": true,
                    "id": 109750,
                    "marker": "" + this.partnerMarker + ".appvk",
                    "origin": {
                        "name": src
                    },
                    "destination": {
                        "name": dst
                    },
                    "color_scheme": {
                        "name": "custom",
                        "icons": "icons_white",
                        "background": "#b3dcf1",
                        "color": "#ffffff",
                        "border_color": "",
                        "button": "#6c8ee0",
                        "button_text_color": "#f9fafb",
                        "input_border": "#59ABE3"
                    },
                    "hotels_type": "hotellook_host",
                    "best_offer": {
                        "locale": "ru",
                        "currency": "rub",
                        "marker": this.partnerMarker + ".appvk",
                        "search_host": "hydra.aviasales.ru",
                        "offers_switch": true,
                        "api_url": "//www.travelpayouts.com/minimal_prices/offers.json",
                        "routes": [
                            {
                                "one_way": false,
                                "origin": {
                                    "name": ""
                                },
                                "destination": {
                                    "name": ""
                                }
                            }
                        ]
                    },
                    "hotel_logo_host": null,
                    "search_logo_host": "www.aviasales.ru",
                    "hotel_marker_format": null,
                    "hotelscombined_marker": null,
                    "responsive": true,
                    "height": 215
                };
            }
        },
    }
</script>

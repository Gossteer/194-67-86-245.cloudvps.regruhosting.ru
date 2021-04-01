<template>
    <el-container v-loading="loading" id="friends" style="padding: 60px 0;">
        <el-main>
            <div>
                <Show
                    v-for="(friend, index) in friends"
                    v-if="
                        friend['deactivated']!='deleted' && friend.city &&
                        friend.city.id != 0 && user.city && user.city.id != 0
                    "
                    :friend="friend"
                    :index="index"
                    :currency="currency"
                    :relations="relations"
                    :user="user"
                    :src-city-code="srcCityCode"
                />
            </div>
            <el-dialog
                :visible.sync="user.city.id === 0"
                custom-class="api-modal-dialog"
                :show-close="false"
                width="80%" :before-close="handleClose">
                <div>
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            Мы покажем стоимость перелета из Вашего города в города друзей. Хотите?
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            Ваша страна
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <el-autocomplete
                                class="countryUser"
                                name="countryUser"
                                v-model="country"
                                :fetch-suggestions="queryCountrySearch"
                                @select="handleCountry"
                                @focus="country=''"
                            ></el-autocomplete>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            Город
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <el-autocomplete
                                class="cityUser"
                                name="cityUser"
                                v-model="city"
                                :fetch-suggestions="queryCitySearch"
                                @select="handleCity"
                            ></el-autocomplete>
                        </el-col>
                    </el-row>
                    <br/>
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            Это окно не будет показано в следующий раз, если Вы укажете свой город в аккаунте
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :xs="24" :sm="5" :md="5" :lg="5" :xl="5">
                            <el-button type="success" class="api__button"
                                       @click.prevent="sendRequestAcception" id="apiFormListModalAccept">
                                Применить
                            </el-button>
                        </el-col>
                    </el-row>
                </div>
            </el-dialog>
        </el-main>
    </el-container>
</template>
<script>
    import {mapActions, mapState} from "vuex";
    import {requestVkApiGet} from "../../helpers/request";
    import {getCityCode} from "../../helpers";
    import Show from "./Show";

    export default {
        data() {
            return {
                loading: false,
                friends: [],
                srcCityCode: '',
                relations: {
                    1: 'не женат/не замужем',
                    2: 'есть друг/есть подруга',
                    3: 'помолвлен/помолвлена',
                    4: 'женат/замужем',
                    5: 'всё сложно',
                    6: 'в активном поиске',
                    7: 'влюблён/влюблена',
                    8: 'в гражданском браке',
                    0: 'не указано'
                },
                currency: 'rub',
                country: 'Россия',
                countryId: 1,
                countries: [],
                city: '',
                cityId: 0,
                cities: [],
                accessToken: '',
            }
        },
        components: {
            Show
        },
        async created() {
            this.loading = true;
            let user = await window.connect.send("VKWebAppGetAuthToken", {
                "app_id": parseInt(window.constants.APP_ID, 10),
                "scope": ""
            });
            if (this.user.city && this.user.city.id === 0) {
                let res = await requestVkApiGet('database.getCountries', {
                    'need_all': 1,
                    'count': 1000
                }, process.env.SERVICE_API_KEY);
                res = res.response;
                if (res) {
                    for (let index in res.items) {
                        this.countries.push({value: res.items[index].title, link: res.items[index].id});
                    }
                }
                await this.makeCitiesList();
            }
            this.accessToken = user.access_token;
            await this.getUserFriends();
            if (this.user.city.id === 0) {
                this.srcCityCode = '';
            } else {
                this.srcCityCode = await getCityCode(this.user.city.title);
            }
            this.loading = false;
        },
        computed: {
            ...mapState({
                user: (state) => state.main.user,
            })
        },
        methods: {
            queryCountrySearch(queryString, cb) {
                let results = queryString ? this.countries.filter(this.createFilter(queryString)) : this.countries;
                cb(results);
            },
            async makeCitiesList() {
                let res = await requestVkApiGet('database.getCities', {
                    'country_id': this.countryId,
                    'need_all': 0,
                    'count': 1000
                }, process.env.SERVICE_API_KEY);
                res = res.response;
                if (res) {
                    this.cities = [];
                    for (let index in res.items) {
                        this.cities.push({value: res.items[index].title, link: res.items[index].id});
                    }
                }
            },
            async handleCountry(item) {
                this.countryId = item.link;
                this.country = item.value;
                this.makeCitiesList();
            },
            queryCitySearch(queryString, cb) {
                let results = queryString ? this.cities.filter(this.createFilter(queryString)) : this.cities;
                cb(results);
            },
            handleCity(item) {
                this.cityId = item.link;
                this.city = item.value;
            },
            createFilter(queryString) {
                return (item) => {
                    return (item.value.toLowerCase().indexOf(queryString.toLowerCase()) === 0);
                };
            },
            async sendRequestAcception() {
                Vue.set(this.user.city, 'id', this.cityId);
                Vue.set(this.user.city, 'title', this.city);
                this.srcCityCode = await getCityCode(this.user.city.title);
            },
            async srcCountryCode(city) {
                return city && city.id !== 0 ? await getCityCode(city.title) : '';
            },
            async getFriendDatName(id) {
                let res = await requestVkApiGet('users.get', {
                    'user_ids': id,
                    'name_case': 'dat'
                }, process.env.SERVICE_API_KEY);
                res = res.response.shift();
                return res.first_name;
            },
            async getUserFriends() {
                let res = await requestVkApiGet('friends.get', {
                    'user_id': parseInt(window.constants.USER_ID, 10),
                    'fields': 'status,bdate,photo_200_orig,city,last_seen,relation,sex'
                }, this.accessToken);
                this.friends = res.response.items;
                for (let index in this.friends) {
                    if (this.friends[index].city && this.friends[index].city.id !== 0) {
                        Vue.set(this.friends[index], 'dstCityCode', await this.srcCountryCode(this.friends[index].city));
                        Vue.set(this.friends[index], 'datName', await this.getFriendDatName(this.friends[index].id));
                        Vue.set(this.friends[index], 'price', null);
                    }
                }
                console.log('this.friends ', this.friends);
            },
            handleClose(done) {
                this.$root.$emit('closeModal', {});
            },
        }
    }
</script>

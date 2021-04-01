<template>
    <el-card
        class="friend-card"
        v-if="friend.city && friend.city.id!==0?user.city.title !== friend.city.title:true"
    >
        <el-row :gutter="10">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <div class="friend-block">
                    <div class="photo">
                        <div class="img-container">
                            <a target="_blank" :href="'https://vk.com/id' + friend.id" style="text-decoration: none;">
                                <img :src="friend.photo_200_orig"/>
                            </a>
                        </div>
                        <div v-if="friend && friend.last_seen" class="last-seen">
                            {{friend.sex===1?'заходила':'заходил'}} {{convertTime(friend.last_seen.time)}}
                        </div>
                    </div>
                    <div class="info">
                        <div class="name">
                            <h3 style="cursor: pointer">
                                <a
                                    target="_blank"
                                    :href="'https://vk.com/id' + friend.id"
                                    style="text-decoration: none;color: #303133"
                                >
                                    {{friend.first_name}}
                                    {{friend.last_name}}
                                </a>
                            </h3>
                            <div class="online">{{friend.online?'online':'offline'}}</div>
                        </div>
                        <div class="status">
                            <i>{{cutStatus(friend.status)}}</i>
                        </div>
                        <div class="bottom">
                            <div class="buttons">
                                <el-button
                                    type="primary"
                                    class="friend-button"
                                    @click.prevent="displayCheapPrice(friend.dstCityCode, index)">
                                    {{friend.price ? friend.price: 'Стоимость полета к ' +
                                    cutName(friend.datName)}}
                                </el-button>
                                <br/>
                                <a class="friend-button" @click="showFriend(friend)">
                                    Календарь низких цен
                                </a>
                            </div>
                            <div class="bottom_info">
                                <div v-if="friend.city" class="city">{{friend.city?friend.city.title:''}}</div>
                                <div v-if="friend.bdate" class="birthday-label">день рождения</div>
                                <div v-if="friend.bdate" class="birthday-value">
                                    {{formatBirthday(friend.bdate)}}
                                </div>
                                <div v-if="friend.relation" class="relationship-status-label">статус</div>
                                <div v-if="friend.relation" class="relationship-status-value">
                                    {{relations[friend.relation]}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br/>
            </el-col>
        </el-row>
    </el-card>
</template>
<script>
    import {mapActions, mapState} from "vuex";
    import {requestVkApiGet} from "../../helpers/request";
    import {getCityAviasalesInfo, getCityCode} from "../../helpers";

    var moment = require('moment');

    export default {
        props: {
            friend: {},
            index: {},
            currency: {},
            relations: {},
            srcCityCode: {},
            user: {},
        },
        data() {
            return {}
        },
        async created() {
        },
        mounted() {
        },
        computed: {
            getDate() {
                return moment().format("YYYY-MM-DD");
            }
        },
        methods: {
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
                let user = await window.connect.send("VKWebAppGetAuthToken", {
                    "app_id": parseInt(window.constants.APP_ID, 10),
                    "scope": ""
                });
                let res = await requestVkApiGet('friends.get', {
                    'user_id': parseInt(window.constants.USER_ID, 10),
                    'fields': 'status,bdate,photo_200_orig,city,last_seen,relation,sex'
                }, user.access_token);
                this.friends = res.response.items;
                for (let index in this.friends) {
                    this.friends[index].dstCityCode = await this.srcCountryCode(this.friends[index].city);
                    this.friends[index].datName = await this.getFriendDatName(this.friends[index].id);
                    this.friends[index].price = null;
                }
                console.log('this.friends ', this.friends);
            },
            cutName(text) {
                if (text && text.length > 9) {
                    text = text.substring(0, 9) + '...';
                }
                return text;
            },
            cutStatus(text) {
                if (text && text.length > 140) {
                    text = text.substring(0, 140) + '...';
                }
                return text;
            },
            convertTime(datetime) {
                let newDatetime = moment(datetime, 'DD-MM-YYYY');
                if (this.isYesterday(newDatetime)) {
                    return 'вчера в ';
                } else {
                    let check = parseInt(moment(datetime).locale('ru').format('DD'), 10);
                    if (check === 19) {
                        let unixtime = new Date(datetime * 1000);
                        let hours = unixtime.getHours();
                        let minutes = "0" + unixtime.getMinutes();
                        return 'сегодня в ' + hours + ':' + minutes.substr(-2);
                    } else {
                        let date = moment(datetime).locale('ru').format('DD MMMM');
                        let time = moment(datetime).format('HH:mm');
                        return date + ' в ' + time;
                    }
                }
            },
            formatBirthday(date) {
                let arr = date.split(".");
                if (arr.length > 2) {
                    date = arr[0] + '/' + arr[1] + '/' + arr[2];
                    return moment(date, 'DD-MM-YYYY').locale('ru').format('DD MMMM YYYY') + ' г.';
                } else {
                    date = arr[0] + '/' + arr[1] + '/1994';
                    let res = moment(date, 'DD-MM-YYYY').locale('ru').format('DD MMMM YYYY');
                    res = res.split(" ");
                    return res[0] + ' ' + res[1];
                }
            },
            isYesterday(momentDate) {
                let yesterday = moment().add(-1, 'days');
                return momentDate.isSame(yesterday, 'd');
            },
            async displayCheapPrice(dstCityCode, index) {
                if (this.srcCityCode && dstCityCode) {
                    let requests = await fetch('/get-cheapest-ticket', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json;charset=utf-8'
                        },
                        body: JSON.stringify({
                            srcCityCode: this.srcCityCode,
                            dstCityCode: dstCityCode,
                            date: this.getDate
                        })
                    });
                    let requestsJson = await requests.json();
                    let res = JSON.parse(requestsJson.data);
                    if (res.success === true) {
                        let data = res.data;
                        data = data[dstCityCode];
                        if (!$.isEmptyObject(data)) {
                            for (let prop in data) {
                                var firstPriceObj = data[prop];
                                break;
                            }
                            Vue.set(this.friend, 'price', firstPriceObj['price'] + ' ' + this.currency);
                        } else {
                            Vue.set(this.friend, 'price', 'Не найдено');
                        }
                    }
                } else {
                    Vue.set(this.friend, 'price', 'Не найдено');
                }
            },
            showFriend(friend) {
                this.$router.push({name: 'edit-search', params: {isFriend: true, friend: friend}});
            },
        },
    }
</script>

<template>
    <el-dialog
        :visible.sync="displayModal"
        custom-class="api-modal-dialog"
        width="80%" :before-close="handleClose">
        <div>
            <ApiData :output-arr="outputArr"/>
            Вы можете изменить параметры, а также отменить подписку в любой момент
            <br>
            Больше не нужно искать дешевые авиабилеты - теперь они найдут нас сами
            <br>
            <el-row :gutter="5">
                <el-col :xs="24" :sm="5" :md="5" :lg="5" :xl="5">
                    <el-button type="primary" class="api__button" @click.prevent="sendRequestAcception"
                               id="apiFormListModalAccept">
                        Применить
                    </el-button>
                </el-col>
                <el-col :xs="24" :sm="5" :md="5" :lg="5" :xl="5">
                    <el-button @click="closeModal" type="primary" class="api__button" id="apiFormListModalDecline">
                        Отменить
                    </el-button>
                </el-col>
                <el-col v-if="wasCreated" :xs="24" :sm="5" :md="5" :lg="5" :xl="5">
                    <el-button @click="deleteRequestModal()" class="api__button" type="danger">
                        Удалить
                    </el-button>
                </el-col>
            </el-row>
        </div>
        <el-dialog
            width="80%"
            title="Удалить эту подписку?"
            :visible.sync="innerVisible"
            append-to-body>
            <el-row :gutter="5">
                <el-col :xs="24" :sm="3" :md="3" :lg="3" :xl="3">
                    <el-button type="primary" @click="deleteRequest()">Да</el-button>
                </el-col>
                <el-col :xs="24" :sm="3" :md="3" :lg="3" :xl="3">
                    <el-button @click="closeInnerModal" type="primary">Нет</el-button>
                </el-col>
            </el-row>
        </el-dialog>
    </el-dialog>
</template>

<script>
    import {mapState, mapActions} from 'vuex';
    import ApiData from "../common/ApiData";

    export default {
        props: {
            form: {
                type: Object,
                required: true
            },
            displayModal: {
                type: Boolean,
                default: false
            },
            optional: {
                type: Boolean,
                default: false
            },
            wasCreated: {
                type: Boolean,
                default: false
            },
            seasonsTranscription: {},
            datesCnt: {},
            showAddionalWeather: {},
            daysOfWeek: {},
            id: {},
            limitsValArr: {},
        },
        data() {
            return {
                outputArr: {},
                innerVisible: false,
            }
        },
        components: {
            ApiData
        },
        watch: {
            displayModal: function (val) {
                if (val === true) {
                    console.log('val', this.form);
                    if (this.form.continentDst) {
                        Vue.set(this.outputArr, 'continentDst', this.form.continentDst);
                    }
                    if (this.form.countrySrcHidden && this.form.countrySrcHidden !== 'любая') {
                        Vue.set(this.outputArr, 'countrySrc', this.form.countrySrc);
                    }
                    if (this.form.citySrcHidden && this.form.citySrcHidden !== 'любой') {
                        Vue.set(this.outputArr, 'citySrc', this.form.citySrcHidden);
                    }
                    if (this.form.countryDstHidden && this.form.countryDstHidden !== 'любая') {
                        Vue.set(this.outputArr, 'countryDst', this.form.countryDst);
                    }
                    if (this.form.cityDstHidden && this.form.cityDstHidden !== 'любой') {
                        Vue.set(this.outputArr, 'cityDst', this.form.cityDstHidden);
                    }
                    Vue.set(this.outputArr, 'interval', this.form.interval);
                    Vue.set(this.outputArr, 'updated', this.form.updated);
                    Vue.set(this.outputArr, 'limit', this.form.limit);
                    Vue.set(this.outputArr, 'forward', this.form.forward);
                    Vue.set(this.outputArr, 'dual', this.form.dual);
                    Vue.set(this.outputArr, 'language', this.form.language);
                    Vue.set(this.outputArr, 'currency', this.form.currency);
                    Vue.set(this.outputArr, 'currencyForUrl', this.form.currencyForUrl);
                    Vue.set(this.outputArr, 'passengers', this.form.passengers);
                    Vue.set(this.outputArr, 'costMin', this.form.costMin);
                    Vue.set(this.outputArr, 'costMax', this.form.costMax);
                    Vue.set(this.outputArr, 'dates', this.form.dates);

                    this.outputArr['dates'] = [];
                    for (let i = 1; i <= this.datesCnt; i++) {
                        Vue.set(this.outputArr['dates'], i - 1, {
                            'year': this.form.date.year[i],
                            'season': this.form.date.season[i],
                            'month': this.form.date.month[i]
                        });
                    }
                    console.log("this.outputArr['dates'] ", this.outputArr['dates']);
                    Vue.set(this.outputArr, 'optional', false);

                    Vue.set(this.outputArr, 'showAddionalWeather', false);
                    if (this.optional) {
                        Vue.set(this.outputArr, 'optional', true);
                        if (this.form.optional.profitability) {
                            Vue.set(this.outputArr, 'profitability', this.form.optional.profitability);
                        }
                        Vue.set(this.outputArr, 'days', []);
                        for (let i in this.form.optional.days) {
                            if (this.form.optional.days[i]) {
                                Vue.set(this.outputArr['days'], i, true);
                            }
                        }
                        if (this.showAddionalWeather) {
                            Vue.set(this.outputArr, 'showAddionalWeather', true);
                            Vue.set(this.outputArr, 'dayTemp.min', this.form.optional.dayTemp.min);
                            Vue.set(this.outputArr, 'dayTemp.max', this.form.optional.dayTemp.max);
                            Vue.set(this.outputArr, 'nightTemp.min', this.form.optional.nightTemp.min);
                            Vue.set(this.outputArr, 'nightTemp.max', this.form.optional.nightTemp.max);
                        }
                    }
                }
            },
        },
        methods: {
            deleteRequestModal() {
                this.innerVisible = true;
            },
            async deleteRequest() {
                let deleter = await fetch('/delete-request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8'
                    },
                    body: JSON.stringify({request_id: this.id})
                });
                let deleterJson = await deleter.json();
                this.innerVisible = true;
                this.$router.push({name: 'subscriptions', params: {initTabSelected: 'api'}});
            },
            sendRequestAcception() {
                let r = Math.random().toString(36).substring(15);
                let groupId = process.env.MIX_MAIN_VK_PUBLIC_ID;
                groupId = parseInt(groupId ? groupId : 192548341, 10);
                console.log("groupId ", groupId);
                console.log(' process.env.MIX_MAIN_VK_PUBLIC_ID', process.env.MIX_MAIN_VK_PUBLIC_ID);
                window.connect
                    .send(
                        'VKWebAppAllowMessagesFromGroup',
                        {'group_id': groupId, 'key': r}
                    ).then(data => {
                    console.log("success!");
                    this.$root.$emit('sendRequestAcception', {outputArr: this.outputArr});
                }).catch(error => {
                    console.log('error ', error);
                });
            },
            closeInnerModal() {
                this.innerVisible = false;
            },
            closeModal() {
                this.$root.$emit('closeModal', {});
            },
            handleClose(done) {
                this.$root.$emit('closeModal', {});
            },
        },
    }
</script>

<template>
    <el-tabs v-model="tabSelected" :tab-position="tabPosition" id="subscriptions">
        <el-alert
            v-if="alertFlag"
            title="Подписка удалена"
            type="success"
            @close="closeAlert">
        </el-alert>
        <el-tab-pane label="Сообщества" name="tags">
            <div id="subscriptionsVk">
                <h2 style="padding: 0 9px;">Сообщества</h2>
                <el-table
                    empty-text="Пусто"
                    class="subscriptionsSubscr"
                    :data="subscriptions"
                    style="width: 100%">
                    <el-table-column prop="category" label="Каталог"></el-table-column>
                    <el-table-column prop="section" label="Раздел"></el-table-column>
                    <el-table-column prop="group_name" label="Описание"></el-table-column>
                    <el-table-column prop="body" label="Место назначения">
                        <template slot-scope="scope">{{ splitCity(scope.row.body) }}</template>
                    </el-table-column>
                    <el-table-column
                        fixed="right"
                        label=""
                        width="120">
                        <template slot-scope="scope">
                            <el-button @click="edit(scope.$index, scope.row)" type="text">
                                <i class="el-icon-edit"></i>
                            </el-button>
                            <el-button @click="remove(scope.$index, scope.row)" type="text">
                                <i class="el-icon-delete"></i>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </el-tab-pane>
        <el-tab-pane label="База данных" name="api">
            <div id="subscriptionsApi">
                <h2>База данных</h2>
                <el-card v-for="(outputArr, index) in apiSubscriptions" :ref="'apiSubscr'+index"
                         class="box-card" shadow="hover">
                    <el-row :gutter="10">
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <ApiData :output-arr="outputArr" :show-title="false"/>
                        </el-col>
                    </el-row>
                    <el-row :gutter="10">
                        <el-col :xs="24" :sm="8" :md="8" :lg="8" :xl="8">
                            <el-button type="primary" class="api__button"
                                       @click="editSubscription(outputArr)">
                                Редактировать
                            </el-button>
                        </el-col>
                        <el-col :xs="24" :sm="8" :md="8" :lg="8" :xl="8">
                            <el-button type="primary" class="api__button"
                                       @click="setApiSubscription(outputArr['id'], index)">
                                Удалить
                            </el-button>
                        </el-col>
                    </el-row>
                </el-card>
                <el-dialog
                    title="Удалить эту подписку?"
                    :visible.sync="dialogVisible"
                    width="80%">
                    <span slot="footer" class="dialog-footer">
                        <el-button type="primary" @click="dialogVisible = false">Нет</el-button>
                        <el-button type="primary" @click="deleteApiSubscription()">Да</el-button>
                    </span>
                </el-dialog>
            </div>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
    import {mapState, mapActions} from 'vuex';
    import ApiData from "../common/ApiData";
    import {isMobile} from "../../helpers";

    export default {
        props: {
            initTabSelected: {
                default: null
            }
        },
        data() {
            return {
                continents: window.constants.CONTINENTS,
                subscriptions: [],
                apiSubscriptions: [],
                seasonsTranscription: window.constants.SEASONS_TRANSCRIPTION,
                monthTranscription: window.constants.MONTHS_TRANSCRIPTION,
                apiRequestId: null,
                dialogVisible: false,
                requestIndex: null,
                daysOfWeek: window.constants.DAYS_OF_WEEK,
                tabSelected: 'tags',
                tabPosition: 'top',
                alertFlag: false
            }
        },
        mounted() {
            console.log('Component mounted.');
        },
        created() {
            if (this.isMobile()) {
                this.tabPosition = 'right';
            }
            this.getUserTags();
            this.getUserRequests();
            if (this.initTabSelected) {
                this.tabSelected = this.initTabSelected;
            }
        },
        computed: {
            ...mapState({
                currencies: (state) => state.main.currencies,
                passengersArr: (state) => state.main.passengers,
                languages: (state) => state.main.languages,
            }),
        },
        components: {
            ApiData
        },
        methods: {
            isMobile() {
                return isMobile();
            },
            closeAlert(e) {
                this.alertFlag = false;
            },
            edit(index, data) {
                let [left, right] = data.body.split('@');
                this.$router.push({
                    name: 'tags',
                    hash: '#' + data.group_name.replace(/ /g, '_'),
                    params: {anchor: right + data.group_name.replace(/ /g, '_')}
                });
            },
            async remove(index, data) {
                let body = {
                    user_id: data.pivot.user_id,
                    tag_id: data.pivot.tag_id
                };
                let response = await fetch('/remove-tag', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8'
                    },
                    body: JSON.stringify(body)
                });
                let result = await response.json();
                console.log("tag was deleted");
                this.subscriptions.splice(index, 1);
                this.alertFlag = true;
            },
            splitCity(cityStr) {
                let cityArr = cityStr.split('@');
                let city = cityArr[0];
                city = city.substring(1);

                return city === 'allIn' || city === 'allOut' ? 'Все' : city;
            },
            async setApiSubscription(id, index) {
                this.apiRequestId = id;
                this.requestIndex = index;
                this.dialogVisible = true;
            },
            editSubscription(outputArr) {
                console.log('outputArr ', outputArr);
                let formObj = {};
                formObj.id = outputArr['id'];
                if (outputArr['countrySrc']) {
                    formObj.countrySrc = outputArr['countrySrc'];
                    formObj.countrySrcHidden = outputArr['countrySrc'];
                }
                if (outputArr['citySrc']) {
                    formObj.citySrc = outputArr['citySrc'];
                    formObj.citySrcHidden = outputArr['citySrc'];
                }
                if (outputArr['continentDst']) {
                    formObj.continentDst = outputArr['continentDst'];
                }
                if (outputArr['countryDst']) {
                    formObj.countryDst = outputArr['countryDst'];
                    formObj.countryDstHidden = outputArr['countryDst'];
                }
                if (outputArr['cityDst']) {
                    formObj.cityDst = outputArr['cityDst'];
                    formObj.cityDstHidden = outputArr['cityDst'];
                }
                formObj.interval = outputArr['interval'];
                formObj.limit = outputArr['limit'];
                formObj.forward = outputArr['forward'];
                formObj.dual = outputArr['dual'];
                formObj.language = outputArr['language'];
                formObj.currency = outputArr['currency'];
                formObj.currencyForUrl = outputArr['currencyForUrl'];
                formObj.passengers = outputArr['passengers'];
                formObj.costMin = outputArr['costMin'];
                formObj.costMax = outputArr['costMax'];

                formObj.date = {year: {}, season: {}, month: {}};
                formObj.datesCnt = 0;
                for (let i in outputArr['dates']) {
                    formObj.datesCnt++;
                    formObj.date.year[parseInt(i, 10) + 1] = (outputArr['dates'][parseInt(i, 10)]['year']);
                    formObj.date.season[parseInt(i, 10) + 1] = (outputArr['dates'][parseInt(i, 10)]['season']);
                    formObj.date.month[parseInt(i, 10) + 1] = (outputArr['dates'][parseInt(i, 10)]['month']);
                }
                if (outputArr['optional']) {
                    formObj.optional = {};
                    formObj.optional.profitability = outputArr['profitability']
                    formObj.optional.days = [];
                    for (let i in outputArr['days']) {
                        formObj.optional.days[i] = outputArr['days'][i];
                    }
                    if (outputArr['showAddionalWeather']) {
                        formObj.optional.dayTemp = {};
                        formObj.optional.dayTemp.min = outputArr['dayTemp.min'];
                        formObj.optional.dayTemp.max = outputArr['dayTemp.max'];
                        formObj.optional.nightTemp = {};
                        formObj.optional.nightTemp.min = outputArr['nightTemp.min'];
                        formObj.optional.nightTemp.max = outputArr['nightTemp.max'];
                    }
                }
                console.log('formObj ', formObj);
                this.$router.push({name: 'edit-api', params: {formProp: formObj}});
            },
            async deleteApiSubscription() {
                let deleter = await fetch('/delete-request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8'
                    },
                    body: JSON.stringify({request_id: this.apiRequestId})
                });
                let deleterJson = await deleter.json();
                this.apiSubscriptions.splice(this.requestIndex, 1);
                this.dialogVisible = false;
            },
            async getUserTags() {
                try {
                    var requests = await fetch('/get-tags', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json;charset=utf-8'
                        },
                        body: JSON.stringify({user_id: window.constants.USER_ID})
                    });
                    var requestsJson = await requests.json();
                    requestsJson.tags.forEach((data) => {
                        this.subscriptions.push(data);
                    });
                } catch (e) {
                    console.log('err ', e);
                }
            },
            async getUserRequests() {
                try {
                    var requests = await fetch('/get-requests', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json;charset=utf-8'
                        },
                        body: JSON.stringify({user_id: window.constants.USER_ID})
                    });
                    var requestsJson = await requests.json();
                    requestsJson.requests.forEach((data) => {
                        let obj = JSON.parse(data.output);
                        obj.id = data.id;
                        obj.limit = data.limit;
                        obj.interval = data.interval;
                        this.apiSubscriptions.push(obj);
                    });
                } catch (e) {
                    console.log('err ', e);
                }
            }
        },
    }
</script>

<template>
    <el-container v-loading="loading" id="admin">
        <el-header class="admin__header">
            <el-row type="flex" justify="center">
                <div style="margin-top: 10px;">
                    Админ-панель
                </div>
            </el-row>
        </el-header>
        <el-main>
            <el-alert
                v-if="alertFlag"
                title="Группа активирована"
                type="success"
                @close="closeAlert">
            </el-alert>
            <el-collapse @change="handleChange">
                <el-collapse-item title="Список групп для активации" name="1">
                    <el-row type="flex" justify="end">
                        <div>
                            <el-link
                                :underline="true"
                                type="warning"
                                @click.prevent="activate">
                                Активировать текущие группы
                            </el-link>
                        </div>
                    </el-row>
                    <el-row type="flex" justify="center">
                        <el-table :data="adminGroups" style="width: 100%">
                            <el-table-column fixed prop="name" label="Название"></el-table-column>
                            <el-table-column fixed="right" label="">
                                <template slot-scope="scope">
                                    <el-button @click="activateOne(scope.row)" type="text" size="small">
                                        Активировать
                                    </el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-row>
                </el-collapse-item>
            </el-collapse>
            <el-form label-position="top" ref="form" :model="form" label-width="120px">
                <el-form-item label="Текст приветствия">
                    <ckeditor :editor="editor" v-model="form.helloText" :config="editorConfig">
                    </ckeditor>
                    <el-row>
                        [[name]] - имя
                    </el-row>
                    <el-row>
                        [[surname]] - фамилия
                    </el-row>
                </el-form-item>
                <el-form-item label="Текст сообщения из API">
                    <el-input
                        type="textarea"
                        :autosize="{ minRows: 2, maxRows: 99999}"
                        placeholder="Введите текст"
                        v-model="form.apiMessageText">
                    </el-input>
                    <el-row>
                        [[srcCity]] - город вылета
                    </el-row>
                    <el-row>
                        [[srcCountry]] - страна вылета
                    </el-row>
                    <el-row>
                        [[arrow]] - стрелочка-разделитель
                    </el-row>
                    <el-row>
                        [[dstCity]] - город прилета
                    </el-row>
                    <el-row>
                        [[dstCountry]] - страна прилета
                    </el-row>
                    <el-row>
                        [[dates]] - даты
                    </el-row>
                    <el-row>
                        [[price]] - цена 　　
                    </el-row>
                    <el-row>
                        [[oldPrice]] - обычная цена
                    </el-row>
                    <el-row>
                        [[discount]] - выгода
                    </el-row>
                    <el-row>
                        [[footer]] - футер
                    </el-row>
                    <el-row>
                        [[tempMin]], [[tempMax]] - температура в день прилета мин-я и макс-я
                    </el-row>
                    <el-row>
                        [[vkAppId]] - ссылка на приложение
                    </el-row>
                    <el-row>
                        [[updated:d]] - дата нахождения авиабилета
                    </el-row>
                    <el-row>
                        [[updated:g]] - дата и время нахождения авиабилета
                    </el-row>
                    <el-row>
                        [[tempSummary]] - дополнительная информация о погоде (например, облачно, с прояснениями)
                    </el-row>
                    <el-row>
                        [[url]] - это ссылка в выбранной валюте и на выбранное кол-во и класс билетов
                    </el-row>
                    <el-row>
                        [[passengers]] - пассажиры
                    </el-row>
                </el-form-item>
                <el-form-item label="Партнерский маркер">
                    <el-input v-model="form.partnerMarker"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click.prevent="onSubmit">
                        Сохранить
                    </el-button>
                </el-form-item>
            </el-form>
        </el-main>
    </el-container>
</template>
<script>
    import {mapActions, mapState} from "vuex";
    import {requestServerPost, requestVkApiGet} from "../../helpers/request";
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import CKEditor from '@ckeditor/ckeditor5-vue';
    import {store} from '../../store';

    Vue.use(CKEditor);

    export default {
        data() {
            return {
                loading: false,
                form: {
                    helloText: HELLO_TEXT,
                    apiMessageText: API_TEXT,
                    partnerMarker: ''
                },
                editor: ClassicEditor,
                editorConfig: {
                    allowedContent: true,
                    removePlugins: ['htmldataprocessor']
                },
                adminGroups: [],
                appId: window.constants.APP_ID,
                alertFlag: false,
            }
        },
        created() {
            this.form.partnerMarker = this.partnerMarker;
        },
        mounted() {
            store.dispatch('main/setLoadingFlag', false);
        },
        computed: {
            ...mapState({
                groups: (state) => state.main.groups,
                partnerMarker: (state) => state.main.partnerMarker,
            }),
        },
        methods: {
            ...mapActions('main', ['getGroupIdFunc', 'setPartnerMarker']),
            closeAlert(e) {
                this.alertFlag = false;
            },
            async onSubmit() {
                this.loading = true;
                let res = await requestServerPost('save-admin', {
                    'hello_text': this.form.helloText,
                    'api_text': this.form.apiMessageText,
                    'partner_marker': this.form.partnerMarker
                });
                this.setPartnerMarker(this.form.partnerMarker);
                this.loading = false;
            },
            sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            },
            handleChange() {
                if (!this.adminGroups.length) {
                    this.loading = true;
                    window.connect.send('VKWebAppGetAuthToken',
                        {'app_id': parseInt(this.appId, 10), 'scope': 'groups'}
                    ).then(async (data) => {
                        try {
                            let accessToken = data.access_token;
                            for (let index in this.groups) {
                                let value = this.groups[index];
                                let groupObj = await this.getGroupIdFunc({params: {group_id: value.code}, accessToken});
                                await this.sleep(200);
                                if (groupObj && groupObj.is_admin) {
                                    this.adminGroups.push(groupObj);
                                }
                                console.log("new group add!");
                            }
                            this.loading = false;
                        } catch (err) {
                            this.loading = false;
                        }
                    });
                }
            },
            async activateOne(groupObj) {
                let groupTokenData = await window.connect.send('VKWebAppGetCommunityAuthToken',
                    {
                        'app_id': parseInt(this.appId, 10),
                        'group_id': parseInt(groupObj.id, 10),
                        'scope': 'manage,messages'
                    });
                await this.setConfirmationCode(parseInt(groupObj.id, 10), groupTokenData.access_token);
                let serverId = await this.addCallbackServer(parseInt(groupObj.id, 10), groupTokenData.access_token);
                let res = await requestVkApiGet('groups.setCallbackSettings', {
                    'group_id': parseInt(groupObj.id, 10),
                    'server_id': serverId,
                    'api_version': '5.103',
                    'wall_post_new': 1,
                    'confirmation': 1,
                    'message_allow': 1,
                }, groupTokenData.access_token);
                if (res.error) {
                    console.log('error ', res.error);
                } else {
                    console.log('success ', res);
                }
                res = await requestVkApiGet('groups.setSettings', {
                    'group_id': parseInt(groupObj.id, 10),
                    'messages': 1,
                    'api_version': '5.103'
                }, groupTokenData.access_token);
                if (res.error) {
                    console.log('error ', res.error);
                } else {
                    let obj = await fetch('/save-chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json;charset=utf-8'
                        },
                        body: JSON.stringify({id: groupObj.id, token: groupTokenData.access_token})
                    });
                    let json = await obj.json();
                    console.log('success ', res);
                }
                this.alertFlag = true;
            },
            async activate() {
                this.loading = true;
                try {
                    for (let index in this.adminGroups) {
                        let groupObj = this.adminGroups[index];
                        if (groupObj && groupObj.is_admin) {
                            this.activateOne(groupObj);
                        }
                        await this.sleep(1000);
                    }
                    this.loading = false;
                } catch (err) {
                    this.loading = false;
                }
            },
            async setConfirmationCode(groupId, accessToken) {
                let resConfirmationCode = await requestVkApiGet('groups.getCallbackConfirmationCode', {
                    'group_id': parseInt(groupId, 10),
                }, accessToken);
                let confirmationCode = resConfirmationCode.response.code;
                let setConfirmationCode = await requestServerPost('setup-confirmation', {
                    'confirmation_code': confirmationCode
                });
                console.log('setConfirmationCode', setConfirmationCode);
            },
            async addCallbackServer(groupId, accessToken) {
                let serversRes = await requestVkApiGet('groups.getCallbackServers', {
                    'group_id': parseInt(groupId, 10),
                }, accessToken);
                console.log("serversRes.response.items ", serversRes.response.items);
                for (let index in serversRes.response.items) {
                    let server = serversRes.response.items[index];
                    let dfgfgfgh = await requestVkApiGet('groups.deleteCallbackServer', {
                        'group_id': parseInt(groupId, 10),
                        'server_id': parseInt(server.id, 10),
                    }, accessToken);
                    console.log("server ", server);
                }
                let serverRes = await requestVkApiGet('groups.addCallbackServer', {
                    'group_id': parseInt(groupId, 10),
                    'url': process.env.MIX_SITE_URL + '/bot',
                    'title': 'botserver'
                }, accessToken);
                console.log("serverRes ", serverRes);
                return serverRes.response.server_id;
            },
        },
    }
</script>

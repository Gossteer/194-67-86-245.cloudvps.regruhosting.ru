<template>
    <el-row :id="'tagId'" :data-name="group.screen_name" class="groupEl">
        <div @mouseover="allClose()">
            <el-row :type="flex" :justify="spaceBetween">
                <el-col :xs="24" :sm="8" :md="8" :lg="8" :xl="8" class="title">
                    {{group.groupTitle.innerHTML}}
                </el-col>
                <el-col :xs="13" :sm="12" :md="12" :lg="12" :xl="12">
                    <a :id="group.screen_name+group.groupTitle.innerHTML.replace(/ /g, '_')"></a>
                    <el-row v-if="show"
                            :type="flex"
                            :justify="spaceBetween"
                            style="margin: 0 !important;"
                            :gutter="15">
                        <el-col :xs="8" :sm="3" style="margin: 0 !important;">
                            <el-row :type="flex" justify="center">
                                <el-tooltip
                                    effect="dark"
                                    content="Подписаться"
                                    placement="top">
                                    <el-button
                                        class="api__button"
                                        @click="subscribeFunc(group)"
                                        size="medium"
                                        type="primary"
                                        icon="el-icon-plus"
                                        circle>
                                    </el-button>
                                </el-tooltip>
                            </el-row>
                        </el-col>
                        <el-col :xs="8" :sm="3" style="margin: 0 !important;">
                            <el-row :type="flex" justify="center">
                                <el-tooltip
                                    effect="dark"
                                    content="Посмотреть"
                                    placement="top">
                                    <a class="tags_button"
                                       target="_blank"
                                       :href="'https://vk.com/' + group.screen_name">
                                        <i class="el-icon-view"></i>
                                    </a>
                                </el-tooltip>
                            </el-row>
                        </el-col>
                        <el-col :xs="8" :sm="3" style="margin: 0 !important;">
                            <el-row :type="flex" justify="center">
                                <Settings :group="group"/>
                            </el-row>
                        </el-col>
                    </el-row>
                </el-col>
            </el-row>
            <el-row v-if="(showTags && show) || afterTagSelect" justify="space-around">
                <el-col>
                    <ul>
                        <p style="font-size: 20px;">Показывать авиабилеты из:</p>
                        <li v-for="tag in tagsOut">
                            <el-checkbox
                                size="medium"
                                name="checkbox[]"
                                :class="'btn' + tag.name"
                                @change="afterTagSelectFunc()"
                                :checked="tagsOut.length < 2"
                                :disabled="tagsOut.length < 2"
                                v-model="checkboxes[tag.name + '/' + tag.group_id + '/' + tag.group_name + '/' + category + '/' + section]"
                                :label="tag.name + '/' + tag.group_id + '/' + tag.group_name + '/' + category + '/' + section">
                                {{splitCity(tag.name)}}
                            </el-checkbox>
                        </li>
                        <li v-if="tagsOut && tagsOut.length > 1">
                            <el-checkbox
                                size="medium"
                                name="checkbox[]"
                                class="btn"
                                @change="afterTagsAllClick(tagsOut, checkboxes['#allOut@cruise_all' + '/' + groupId + '/' + group.groupTitle.innerHTML + '/' + category + '/' + section])"
                                v-model="checkboxes['#allOut@cruise_all' + '/' + groupId + '/' + group.groupTitle.innerHTML + '/' + category + '/' + section]"
                                :label="'#allOut@cruise_all' + '/' + groupId + '/' + group.groupTitle.innerHTML + '/' + category + '/' + section">
                                все
                            </el-checkbox>
                        </li>
                    </ul>
                </el-col>
                <el-col>
                    <ul>
                        <p style="font-size: 20px;">Показывать авиабилеты в:</p>
                        <li v-for="tag in tagsIn">
                            <el-checkbox
                                size="medium"
                                name="checkbox[]"
                                :class="'btn' + tag.name"
                                @change="afterTagSelectFunc()"
                                :checked="tagsIn.length < 2"
                                :disabled="tagsIn.length < 2"
                                v-model="checkboxes[tag.name + '/' + tag.group_id + '/' + tag.group_name + '/' + category + '/' + section]"
                                :label="tag.name + '/' + tag.group_id + '/' + tag.group_name + '/' + category + '/' + section">
                                {{splitCity(tag.name)}}
                            </el-checkbox>
                        </li>
                        <li v-if="tagsIn && tagsIn.length > 1">
                            <el-checkbox
                                size="medium"
                                name="checkbox[]"
                                class="btn"
                                @change="afterTagsAllClick(tagsIn, checkboxes['#allIn@cruise_all' + '/' + groupId + '/' + group.groupTitle.innerHTML + '/' + category + '/' + section])"
                                v-model="checkboxes['#allIn@cruise_all' + '/' + groupId + '/' + group.groupTitle.innerHTML + '/' + category + '/' + section]"
                                :label="'#allIn@cruise_all' + '/' + groupId + '/' + group.groupTitle.innerHTML + '/' + category + '/' + section">
                                все
                            </el-checkbox>
                        </li>
                    </ul>
                </el-col>
            </el-row>
        </div>
    </el-row>
</template>
<script>
    import Settings from './Settings';
    import {mapState, mapActions} from 'vuex';
    import {requestVkApiGet} from "../../../helpers/request";
    import {extractLocationFromTag} from "../../../helpers/tags";
    import {isMobile} from "../../../helpers";

    export default {
        props: {
            group: {},
            id: {},
            category: {
                default: 'Любая'
            },
            section: {
                default: 'Любой'
            },
        },
        data() {
            return {
                showTags: false,
                afterTagSelect: false,
                tagsIn: [],
                tagsOut: [],
                show: true,
                flex: this.isMobile() ? '' : 'flex',
                spaceBetween: this.isMobile() ? '' : 'space-around',
                userTags: [],
                groupId: ''
            }
        },
        components: {
            Settings
        },
        created() {
            this.show = false;
            this.$root.$on('openAnchor_' + this.group.screen_name, (data) => {
                this.getTagsList();
                this.show = true;
                this.showTags = true;
                this.afterTagSelect = true;
            });
        },
        async mounted() {
            this.$root.$on('getTags' + this.group.id, async (data) => {
                await this.getTagsList();
                this.afterTagSelect = true;
                let selector = '#' + this.group.screen_name + this.group.groupTitle.innerHTML.replace(/ /g, '_');
                let scrollTop = $('#tags form .el-collapse').scrollTop();
                $('#tags form .el-collapse').animate({
                    scrollTop: -scrollTop
                }, 1);
                setTimeout(() => {
                    scrollTop = $('#tags form .el-collapse').scrollTop();
                    console.log("li ", scrollTop);
                    let li = $(selector);
                    $('#tags form .el-collapse').animate({
                        scrollTop: li.offset().top - 70
                    }, 10);
                }, 500);
            });
            this.$root.$on('allClose', (data) => {
                this.show = false;
            });
        },
        destroyed() {
            this.$root.$off('getTags' + this.group.id)
        },
        computed: {
            ...mapState({
                checkboxes: (state) => state.main.tags
            }),
        },
        methods: {
            ...mapActions('main', ['getGroupIdFunc']),
            afterTagsAllClick(tags, checkbox) {
                if (checkbox) {
                    for (let index in tags) {
                        let tag = tags[index];
                        this.checkboxes[tag.name + '/' + tag.group_id + '/' + tag.group_name + '/' + this.category + '/' + this.section] = true;
                    }
                }
                this.afterTagSelectFunc();
            },
            afterTagSelectFunc() {
                this.afterTagSelect = true;
            },
            splitCity(cityStr) {
                let cityArr = cityStr.split('@');
                let city = cityArr[0];

                return city.substring(1);
            },
            isMobile() {
                return isMobile();
            },
            allClose() {
                this.$root.$emit('allClose', {});
                this.show = true;
            },
            getGroupId(screenName) {
                return requestVkApiGet('groups.getById', {'group_id': screenName});
            },
            async getTagsList() {
                let tagsIn = [];
                let tagsOut = [];
                let groupId = await this.getGroupId(this.group.screen_name);
                groupId = this.groupId = groupId.response[0].id;
                let r = Math.random().toString(36).substring(15);
                window.connect
                    .send(
                        'VKWebAppAllowMessagesFromGroup',
                        {'group_id': groupId, 'key': r}
                    ).then(data => {
                    requestVkApiGet('wall.get', {
                        'domain': this.group.screen_name
                    }).then((data) => {
                        let itemsWall = data.response.items;
                        let dublicatesBufferIn = [];
                        let dublicatesBufferOut = [];
                        itemsWall.forEach((group) => {
                            let match = [...group.text.matchAll(/(#.*@.*)/g)];
                            if (match.length) {
                                console.log('match', match);
                                if (match.length > 2) {
                                    let matchOut = match[2];
                                    extractLocationFromTag(matchOut, groupId, this.group, dublicatesBufferOut, tagsOut);
                                }
                                let matchIn = match[0];
                                extractLocationFromTag(matchIn, groupId, this.group, dublicatesBufferIn, tagsIn);
                            }
                        });
                        this.showTags = true;
                        this.tagsOut = tagsOut;
                        this.tagsIn = tagsIn;
                        console.log('this.checkboxes ', this.checkboxes);
                    });
                }).catch(error => {
                    console.log('error ', error);
                });
            },
            async subscribeFunc(group) {
                console.log('group.group_name', this.group);
                let groupObj = await this.getGroupIdFunc({
                    params: {
                        'group_id': this.group.screen_name,
                    },
                    accessToken: 'dc23d677dc23d677dc23d67752dc4dc5f4ddc23dc23d67781f080bc544ea3e49a35d666'
                });
                let groupId = groupObj.id;
                let body = {
                    user_id: window.constants.USER_ID,
                    app_id: window.constants.APP_ID,
                    checkbox: groupId
                };
                let response = await fetch('/save-groups', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8'
                    },
                    body: JSON.stringify(body)
                });
                let result = await response.json();
                window.connect
                    .send(
                        'VKWebAppJoinGroup',
                        {'group_id': groupId}
                    ).then(data => {
                    console.log(data);
                }).catch(error => {
                    console.log('error ', error);
                });
                console.log('subscribe success');
            },
        },
    }
</script>

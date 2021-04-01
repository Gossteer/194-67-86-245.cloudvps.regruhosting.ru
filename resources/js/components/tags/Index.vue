<template>
    <div v-loading="loading || globalLoading" id="tags" class="show"
         v-bind:class="{ zeroZ: !dialogVisible }">
        <form>
            <el-collapse v-model="collapseName" style="-webkit-overflow-scrolling: touch;">
                <Block v-for="(item, index) in groups"
                       :index="index"
                       :item="item"
                       :anchor="anchor"
                       :ref="'block' + index">
                </Block>
            </el-collapse>
            <input type="hidden" :value="userId" name="user_id"/>
            <input type="hidden" :value="appId" name="app_id"/>
            <el-row>
                <el-col class="button-wrap" :xs="24" :sm="8" :md="8" :lg="8" :xl="8">
                    <el-button @click="dialogVisible = true" type="primary" class="button api__button">
                        Применить
                    </el-button>
                </el-col>
            </el-row>
            <el-dialog
                title="Присылать сообщения из сообщества (название сообщества или сообществ)?"
                :visible.sync="dialogVisible"
                width="80%">
                    <span slot="footer" class="dialog-footer">
                        <el-button type="primary" @click="dialogVisible = false">Отменить</el-button>
                        <el-button type="primary" @click="sendTags()">Применить</el-button>
                    </span>
            </el-dialog>
        </form>
    </div>
</template>

<script>
    import Block from './block/Block';
    import {mapState, mapActions} from 'vuex';
    import {store} from "../../store";

    export default {
        props: {
            anchor: {
                type: String,
                default: null
            }
        },
        data() {
            return {
                matches: [],
                loading: false,
                groups: {},
                userId: window.constants.USER_ID,
                appId: window.constants.APP_ID,
                collapseName: '',
                dialogVisible: false
            }
        },
        components: {
            Block
        },
        computed: {
            ...mapState({
                groupsArr: (state) => state.main.groups,
                globalLoading: (state) => state.main.loading,
                checkboxes: (state) => state.main.tags
            }),
        },
        async mounted() {
            this.$root.$on('openCollapse', (data) => {
                this.collapseName = data.name;
            });
            store.dispatch('main/setLoadingFlag', false);
            this.loading = true;
            await this.getGroups();

            if (this.anchor) {
                let group = $(`#${this.anchor}`).closest('#groupCatalog');
                let name = group.data('name');
                this.$root.$emit('openCollapse', {name: name});

                let section = $(`#${this.anchor}`).closest('#groupSection');
                if (section) {
                    let nameSection = section.data('name');
                    this.$root.$emit('openSectionCollapse', {name: nameSection});
                }

                let li = $(`#${this.anchor}`);
                setTimeout(() => {
                    $('#tags form .el-collapse').animate({
                        scrollTop: li.offset().top - 70
                    }, 10);
                    let tag = $(`#${this.anchor}`).closest('#tagId');
                    let tagName = tag.data('name');
                    this.$root.$emit('openAnchor_' + tagName);
                }, 1000);
            }
        },
        created() {
            this.getUserTags();
        },
        methods: {
            ...mapActions('main', ['getGroupsFunc', 'getCheckboxes']),
            async sendTags(e) {
                let checkboxesArr = [];
                for (let index in this.checkboxes) {
                    if (this.checkboxes[index]) {
                        checkboxesArr.push(index);
                    }
                }
                let body = {
                    user_id: window.constants.USER_ID,
                    checkbox: checkboxesArr
                };
                console.log("body ", body);
                let response = await fetch('/save-tags', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8'
                    },
                    body: JSON.stringify(body)
                });
                let result = await response.json();
                // document.querySelector('#tags form .el-collapse').scrollTo(0, 0);
                //   window.scrollTo(0, 0);
                this.dialogVisible = false;
                console.log("tags was saved!");
            },
            async getUserTags() {
                this.getCheckboxes(window.constants.USER_ID);
            },
            async getGroups() {
                await this.getGroupsFunc({});
                this.makeGroupsList(this.groupsArr);
                this.loading = false;
            },
            makeGroupsList(items) {
                items.forEach((value) => {
                    let obj = {
                        id: '',
                        groupTitle: {
                            innerHTML: {},
                        },
                        screen_name: ''
                    };
                    let groupName = value.description;
                    obj.id = value.id.toString(10);
                    obj.groupTitle.innerHTML = groupName;
                    obj.screen_name = value.code;
                    if (value.catalog) {
                        if (!this.groups[value.catalog]) {
                            this.groups[value.catalog] = {};
                        }
                        if (value.section) {
                            if (!this.groups[value.catalog][value.section]) {
                                this.groups[value.catalog][value.section] = [];
                            }
                            this.groups[value.catalog][value.section].push(obj);
                        } else {
                            this.groups[value.catalog][obj.id] = obj;
                        }
                    }
                });
            }
        }
    }
</script>

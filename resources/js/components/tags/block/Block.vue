<template>
    <el-collapse-item id="groupCatalog" @change="allClose()" :title="index"
                      :data-name="index"
                      :name="index">
        <el-collapse
                v-model="collapseName"
                style="height: auto !important; -webkit-overflow-scrolling: touch;"
        >
            <div v-for="(groupsObj, name) in item">
                <Section v-if="Array.isArray(groupsObj)" :section="groupsObj" :name="name" :category="index"/>
                <Group v-else :group="groupsObj" :ref="'group' + name" :category="index"/>
            </div>
        </el-collapse>
    </el-collapse-item>
</template>

<script>
    import Group from './Group';
    import Section from './Section';

    export default {
        props: {
            item: {},
            index: {},
            anchor: {
                type: String,
                default: null
            }
        },
        data() {
            return {
                collapseName: ''
            }
        },
        mounted() {
            this.$root.$on('openSectionCollapse', (data) => {
                this.collapseName = data.name;
            });
        },
        components: {
            Group,
            Section
        },
        methods: {
            allClose() {
                this.$root.$emit('allClose', {});
            }
        }
    }
</script>

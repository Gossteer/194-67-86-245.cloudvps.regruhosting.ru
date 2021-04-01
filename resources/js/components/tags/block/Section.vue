<template>
    <el-collapse-item id="groupSection" :data-name="name" :title="name" :name="name">
        <Group v-for="groupsObj in section" :group="groupsObj"
               :section="name"
               :category="category"
        />
    </el-collapse-item>
</template>

<script>
    import Group from './Group';
    import Section from './Section';

    export default {
        props: {
            section: {},
            name: {},
            category: {
                default: ''
            },
        },
        created() {
        },
        components: {
            Group,
            Section
        },
        methods: {}
    }
</script>

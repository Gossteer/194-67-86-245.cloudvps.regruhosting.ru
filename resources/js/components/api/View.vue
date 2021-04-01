<template>
    <Index :form-prop="formProp"/>
</template>
<script>
    import Index from "./Index";

    export default {
        props: {
            formProp: {
                type: Object,
                default: null
            }
        },
        data() {
            return {}
        },
        components: {
            Index
        },
        created() {
        },
        async mounted() {
        },
        destroyed() {
        },
        computed: {},
        watch: {},
        methods: {}
    }
</script>

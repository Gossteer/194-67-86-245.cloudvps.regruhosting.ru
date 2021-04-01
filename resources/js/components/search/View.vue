<template>
    <Index :is-friend="isFriend" :friend="friend"/>
</template>

<script>
    import Index from "./Index";

    export default {
        props: {
            isFriend: {
                type: Boolean,
                default: false
            },
            friend: {
                type: Object,
                default: null
            }
        },
        mounted() {
        },
        components: {
            Index
        }
    }
</script>

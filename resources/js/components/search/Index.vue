<template>
    <el-main id="searchString">
        <Friend :is-friend="isFriend" :friend="friend"/>
    </el-main>
</template>

<script>
    import Friend from "./Friend";

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
            Friend
        }
    }
</script>

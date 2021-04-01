<template>
    <el-tooltip :placement="isMobile()?'top':'left-start'" popper-class="tooltip-custom">
        <div @mouseover="show = true" slot="content">
            Вы можете получать со стены сообщества в личные сообщения только те авиабилеты,
            <br>
            которые Вам интересны.
            <br>
            <a @click.prevent="getTagsList" class="tooltiptextHref" href="#">
                Для этого разрешите сейчас сообщения в сообществе
            </a>
            <br>
            и выберите города прилета из списка:
        </div>
        <el-button @click.prevent="emitShowTags"
                   size="medium"
                   type="primary"
                   icon="el-icon-setting"
                   circle>
        </el-button>
    </el-tooltip>
</template>

<script>
    import {isMobile} from "../../../helpers";

    export default {
        props: ['group'],
        data() {
            return {}
        },
        mounted() {
        },
        methods: {
            isMobile() {
                return isMobile();
            },
            emitShowTags() {
                this.$root.$emit('getTags' + this.group.id, {tags: [], showTags: false});
            },
            async getTagsList(e) {
                this.$root.$emit('getTags' + this.group.id);
            },
        }
    }
</script>

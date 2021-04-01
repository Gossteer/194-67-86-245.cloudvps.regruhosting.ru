<template>
    <div class="container">
        <NavBar/>
        <router-view></router-view>
        <el-dialog
            title="Добро пожаловать"
            :visible.sync="dialogVisible"
            @close="disableModal()"
            width="80%">
            <span v-html="helloText"></span>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="disableModal()">
                    Больше не показывать
                </el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
    import NavBar from './NavBar';
    import {mapState, mapActions} from "vuex";

    export default {
        data() {
            return {
                dialogVisible: true,
                helloText: HELLO_TEXT?HELLO_TEXT:''
            }
        },
        async created() {
            if (localStorage.modalHide) {
                this.dialogVisible = false;
            }
            await this.getUserInfo();
            let firstName = this.user.first_name;
            let lastName = this.user.last_name;
            this.helloText = this.helloText.replace('[[name]]', firstName);
            this.helloText = this.helloText.replace('[[surname]]', lastName);
        },
        mounted() {
        },
        components: {
            NavBar
        },
        computed: {
            ...mapState({
                user: (state) => state.main.user,
            }),
        },
        methods: {
            ...mapActions('main', ['getUserInfo']),
            async disableModal() {
                this.dialogVisible = false;
                localStorage.modalHide = true;
            }
        }
    }
</script>

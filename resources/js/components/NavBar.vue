<template>
    <div v-if="!isMobile()" class="top">
        <el-menu
            :default-active="activeIndex"
            class="el-menu-demo"
            mode="horizontal"
            background-color="white"
            text-color="#656565"
            :router="true"
            active-text-color="#409EFF">
            <el-menu-item class="nav-button" index="1" :route="{name:'tags'}">Сообщества</el-menu-item>
            <el-menu-item class="nav-button" index="2" :route="{name:'api'}">Настройки</el-menu-item>
            <el-menu-item class="nav-button" index="3" :route="{name:'subscriptions'}">Мои подписки</el-menu-item>
            <el-menu-item class="nav-button" index="4" :route="{name:'friends'}">Друзья</el-menu-item>
            <el-menu-item class="nav-button" index="5" :route="{name:'search'}">Aviasales</el-menu-item>
            <el-menu-item class="nav-button" v-if="isAdmin()" index="6" :route="{name:'admin'}">Админ</el-menu-item>
        </el-menu>
    </div>
    <div v-else class="top mobile">
        <Slide>
            <router-link class="nav-button" id="tagsButton" :to="{name: 'tags'}">
                Сообщества
            </router-link>
            <router-link class="nav-button" id="apiButton" :to="{name: 'api'}">
                Настройки
            </router-link>
            <router-link class="nav-button" id="subscriptionsButton" :to="{name: 'subscriptions'}">
                Мои подписки
            </router-link>
            <router-link class="nav-button" id="friendsButton" :to="{name: 'friends'}">
                Друзья
            </router-link>
            <router-link class="nav-button" id="searchStringButton" :to="{name: 'search'}">
                Aviasales
            </router-link>
            <router-link
                v-if="isAdmin()"
                class="nav-button"
                id="adminButton"
                :to="{name: 'admin'}">
                Админ
            </router-link>
        </Slide>
    </div>
</template>

<script>
    import {Slide} from 'vue-burger-menu';
    import {isMobile} from "../helpers";

    export default {
        data() {
            return {
                activeIndex: '1',
            }
        },
        components: {
            Slide
        },
        mounted() {
            console.log('Component mounted.')
        },
        methods: {
            isAdmin() {
                return window.constants.USER_ROLE == window.constants.USER_ROLE_ADMIN
            },
            isMobile() {
                return isMobile();
            }
        }
    }
</script>

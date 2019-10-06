<template>
    <v-navigation-drawer
        fixed
        app
        v-model="drawer"
        :mini-variant.sync="mini"
    >
        <layout-sidebar-logo/>
        <layout-sidebar-profile/>
        <layout-sidebar-menu/>
    </v-navigation-drawer>
</template>
<script>
    import Logo from '~/components/user/layout/sidebar/logo';
    import Profile from '~/components/user/layout/sidebar/profile';
    import Menu from '~/components/user/layout/sidebar/menu';

    export default {
        name: 'layout-sidebar',
        components: {
            'layout-sidebar-logo': Logo,
            'layout-sidebar-profile': Profile,
            'layout-sidebar-menu': Menu,
        },
        data: () => ({
            drawer: null,
            mini: false
        }),
        mounted() {
            this.$bus.$on('toggleDrawer', () => {
                this.drawer = !this.drawer;
            });
            this.$bus.$on('minimizeDrawer', () => {
                this.mini = !this.mini;
            });
        }
    };
</script>

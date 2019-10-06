<template>
    <v-navigation-drawer
        fixed
        app
        v-model="drawer"
        :mini-variant.sync="mini"
    >
        <dashboard-layout-sidebar-logo/>
        <dashboard-layout-sidebar-profile/>
        <dashboard-layout-sidebar-menu/>
    </v-navigation-drawer>
</template>
<script>
    import Profile from '~/components/dashboard/layout/sidebar/profile';
    import Logo from '~/components/dashboard/layout/sidebar/logo';
    import Menu from '~/components/dashboard/layout/sidebar/menu';

    export default {
        name: 'dashboard-layout-sidebar',
        components: {
            'dashboard-layout-sidebar-profile': Profile,
            'dashboard-layout-sidebar-logo': Logo,
            'dashboard-layout-sidebar-menu': Menu
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

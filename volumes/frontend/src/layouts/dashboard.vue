<template>
    <v-app>
        <dashboard-layout-header/>
        <dashboard-layout-sidebar/>
        <dashboard-layout-content/>
    </v-app>
</template>
<script>
    import Header from '~/components/dashboard/layout/header';
    import Sidebar from '~/components/dashboard/layout/sidebar';
    import Content from '~/components/dashboard/layout/content';

    export default {
        name: 'dashboard-layout',
        components: {
            'dashboard-layout-header': Header,
            'dashboard-layout-sidebar': Sidebar,
            'dashboard-layout-content': Content,
        }
    };
</script>
<style lang="scss">
    $base-background: #232d35;
    $content-background: #171e26;

    .theme--dark {
        .v-card {
            background-color: $base-background!important;
        }
    }
    .v-navigation-drawer {
        background: $base-background!important;
    }
    .v-content__wrap {
        background: $content-background!important;
    }
</style>

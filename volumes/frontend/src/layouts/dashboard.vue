<template>
    <v-app>
        <dashboard-layout-header/>
        <dashboard-layout-sidebar/>
        <dashboard-layout-content/>
    </v-app>
</template>
<script>
    import { mapGetters } from 'vuex';

    import Header from '~/components/dashboard/layout/header';
    import Sidebar from '~/components/dashboard/layout/sidebar';
    import Content from '~/components/dashboard/layout/content';

    export default {
        name: 'dashboard-layout',
        middleware: ['auth', 'administrator'],
        components: {
            'dashboard-layout-header': Header,
            'dashboard-layout-sidebar': Sidebar,
            'dashboard-layout-content': Content,
        },
        computed: {
            ...mapGetters({
                token: 'account/token',
                token_type: 'account/token_type',
            }),
        },
        mounted() {
            this.updateEchoConfiguration();
        },
        methods: {
            updateEchoConfiguration() {
                const tokenString = `${this.token_type} ${this.token}`;
                this.$echo.options.auth.headers.Authorization = tokenString;
                this.$echo.connector.pusher.config.auth.headers.Authorization = tokenString;
                return this;
            },
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

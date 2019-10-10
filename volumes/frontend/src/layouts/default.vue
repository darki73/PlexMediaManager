<template>
    <v-app>
        <layout-header/>
        <layout-sidebar/>
        <layout-content/>
    </v-app>
</template>
<script>
    import { mapGetters } from 'vuex';
    import Header from '~/components/user/layout/header';
    import Sidebar from '~/components/user/layout/sidebar';
    import Content from '~/components/user/layout/content';

    export default {
        name: 'default-layout',
        components: {
            'layout-header': Header,
            'layout-sidebar': Sidebar,
            'layout-content': Content,
        },
        computed: {
            ...mapGetters({
                global_authenticated: 'account/authenticated',
                global_token: 'account/token',
                global_token_type: 'account/token_type',
            }),
        },
        watch: {
            global_authenticated (current, previous) {
                if (current === true && previous === false) {
                    this.updateEchoConfiguration();
                }
            }
        },
        mounted() {
            if (this.global_authenticated) {
                this.updateEchoConfiguration();
            }
        },
        methods: {
            updateEchoConfiguration() {
                const tokenString = `${this.global_token_type} ${this.global_token}`;
                this.$echo.options.auth.headers.Authorization = tokenString;
                this.$echo.connector.pusher.config.auth.headers.Authorization = tokenString;
            }
        }
    };
</script>

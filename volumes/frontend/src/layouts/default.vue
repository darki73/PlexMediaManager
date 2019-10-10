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
        data: () => ({

        }),
        computed: {
            ...mapGetters({
                global_authenticated: 'account/authenticated',
                global_token: 'account/token',
                global_token_type: 'account/token_type',

                isPlexAuthenticated: 'account/plex_authenticated',
                selectedPlexServer: 'plex/selected_server',
                plexLibraries: 'plex/libraries'
            }),
        },
        watch: {
            global_authenticated (current, previous) {
                if (current === true && previous === false) {
                    this.updateEchoConfiguration();
                }
            },
            selectedServer() {
                if (this.isPlexAuthenticated) {
                    this.$bus.$emit('refreshPlexLibrariesList');
                }
            }
        },
        mounted() {
            if (this.global_authenticated) {
                this.updateEchoConfiguration();
            }

            if (this.isPlexAuthenticated) {
                if (
                    this.selectedPlexServer !== null
                    && this.selectedPlexServer !== undefined
                ) {
                    this.$store.dispatch('plex/fetchLibrariesList', this.selectedPlexServer);
                }
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

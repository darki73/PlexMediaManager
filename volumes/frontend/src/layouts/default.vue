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
                authenticated: 'account/authenticated',
                user: 'account/user',
                token: 'account/token',
                token_type: 'account/token_type',

                isPlexAuthenticated: 'account/plex_authenticated',
                selectedPlexServer: 'plex/selected_server',
                plexLibraries: 'plex/libraries'
            }),
        },
        watch: {
            authenticated (current, previous) {
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
            if (this.authenticated) {
                this
                    .updateEchoConfiguration()
                    .joinToPublicEchoChannels()
                    .joinToPrivateEchoChannels();

                if (this.isPlexAuthenticated) {
                    if (
                        this.selectedPlexServer !== null
                        && this.selectedPlexServer !== undefined
                    ) {
                        this.$store.dispatch('plex/fetchLibrariesList', this.selectedPlexServer);
                    }
                }
            }
        },
        methods: {
            updateEchoConfiguration() {
                const tokenString = `${this.token_type} ${this.token}`;
                this.$echo.options.auth.headers.Authorization = tokenString;
                this.$echo.connector.pusher.config.auth.headers.Authorization = tokenString;
                return this;
            },
            joinToPublicEchoChannels() {
                return this;
            },
            joinToPrivateEchoChannels() {
                if (this.isAdministrator(this.user)) {
                    this.$echo.private('account.admins').listen('.requests.new_request', (event) => {
                        this.$bus.$emit('showNewNotification', this.$t('notification.request.created', {
                            username: event.request.user.username,
                            type: this.$t('notification.request.' + event.request.request_type),
                            title: event.request.title,
                            year: event.request.year,
                            date: event.request.created_at
                        }));
                    });
                }
                return this;
            }
        }
    };
</script>

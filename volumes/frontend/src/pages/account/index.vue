<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-layout row wrap>
                <v-flex xs12 lg4 xl2>
                    <v-card>
                        <v-card-text>
                            <v-layout row wrap>
                                <!-- Avatar Block Start -->
                                <v-flex xs12 text-center>
                                    <v-avatar tile size="120">
                                        <v-img :src="user.avatar_url"/>
                                    </v-avatar>
                                </v-flex>
                                <!-- Avatar Block End -->

                                <!-- Username Block Start -->
                                <v-flex xs12 text-center class="headline white--text">
                                    @{{ user.username }}
                                </v-flex>
                                <!-- Username Block End -->

                                <!-- Email Block Start -->
                                <v-flex xs12 text-center class="italic">
                                    {{ user.email }}
                                </v-flex>
                                <!-- Email Block End -->

                                <!-- Social Information | Buttons Start -->
                                <v-flex xs12>
                                    <v-layout row wrap>
                                        <!-- TODO: Add condition when user already logged in with Plex/Google -->

                                        <!-- (Not Logged In) Google OAuth Button Start -->
                                        <v-flex xs12 v-show="false">
                                            <v-btn
                                                block
                                                color="grey darken-4"
                                                class="normal-case"
                                                disabled
                                            >
                                                <v-icon left color="primary lighten-1">
                                                    mdi-google
                                                </v-icon>
                                                {{ $t('common.log_in_with_google') }}
                                            </v-btn>
                                        </v-flex>
                                        <!-- (Not Logged In) Google OAuth Button End -->

                                        <!-- (Not Logged In) Plex OAuth Button Start -->
                                        <v-flex xs12 v-if="!isPlexAuthenticated">
                                            <v-btn
                                                block
                                                color="grey darken-4"
                                                class="normal-case"
                                                @click="signInWithPlex"
                                                :loading="authenticatingWithPlex"
                                            >
                                                <v-icon left color="amber darken-2">
                                                    mdi-plex
                                                </v-icon>
                                                {{ $t('common.log_in_with_plex') }}
                                            </v-btn>
                                        </v-flex>
                                        <!-- (Not Logged In) Plex OAuth Button End -->

                                        <!-- (Logged In) Plex OAuth Button Start -->
                                        <v-flex xs12 v-if="isPlexAuthenticated">
                                            <v-btn
                                                block
                                                class="normal-case"
                                                disabled
                                            >
                                                <v-icon left color="amber darken-2">
                                                    mdi-plex
                                                </v-icon>
                                                {{ $t('common.logged_in_with_plex') }}
                                            </v-btn>
                                        </v-flex>
                                        <!-- (Logged In) Plex OAuth Button End -->
                                    </v-layout>
                                </v-flex>
                                <!-- Social Information | Buttons End -->

                            </v-layout>
                        </v-card-text>
                    </v-card>
                </v-flex>
                <v-slide-x-transition>
                    <v-flex xs12 lg4 v-show="isPlexAuthenticated">
                        <v-card>
                            <v-card-title>
                                <span v-show="!fetchingPlexServers && plexServers.length > 0">
                                    {{ $t('user.plex.servers.servers') }}
                                </span>
                                <v-spacer/>
                                <v-btn
                                    v-show="!fetchingPlexServers && plexServers.length > 0"
                                    @click.stop="refreshPlexServersList(true)"
                                    color="primary"
                                    small
                                >
                                    {{ $t('common.refresh') }}
                                </v-btn>
                            </v-card-title>
                            <v-card-text>
                                <v-layout row wrap text-center v-show="fetchingPlexServers">
                                    <!-- [Plex::fetchingServers] Circular Progress Start -->
                                    <v-flex xs12>
                                        <v-progress-circular
                                            :size="100"
                                            :width="5"
                                            color="primary"
                                            indeterminate
                                        />
                                    </v-flex>
                                    <!-- [Plex::fetchingServers] Circular Progress End -->

                                    <!-- [Plex::fetchingServers] Progress Message Start -->
                                    <v-flex xs12 class="pt-3">
                                        {{ $t('user.plex.servers.fetching') }}
                                    </v-flex>
                                    <!-- [Plex::fetchingServers] Progress Message End -->
                                </v-layout>
                                <v-layout row wrap v-show="!fetchingPlexServers">
                                    <!-- [Plex::noServers] Block Start -->
                                    <v-flex xs12 v-if="plexServers.length === 0">
                                        <v-layout row wrap>
                                            <!-- [Plex::noServers] Icon Start -->
                                            <v-flex xs12 text-center>
                                                <v-icon :size="100">
                                                    mdi-server-off
                                                </v-icon>
                                            </v-flex>
                                            <!-- [Plex::noServers] Icon End -->

                                            <!-- [Plex::noServers] Text Start -->
                                            <v-flex xs12 text-center class="pt-3">
                                                {{ $t('user.plex.servers.no_servers') }}
                                            </v-flex>
                                            <!-- [Plex::noServers] Text End -->
                                        </v-layout>
                                    </v-flex>
                                    <!-- [Plex::noServers] Block End -->

                                    <!-- [Plex::servers] Block Start -->
                                    <v-flex xs12 v-else>
                                        <!-- Plex Server List Item Start -->
                                        <v-list-item
                                            two-line
                                            v-for="(server, index) in plexServers"
                                            :key="`list-entry-for-server-with-index-${index}`"
                                        >
                                            <!-- Server Switch (Selector) Input Start -->
                                            <v-list-item-avatar>
                                                <v-switch
                                                    color="primary"
                                                    :input-value="selectedServer"
                                                    :value="server.id"
                                                    @change="selectNewPreferredServer(server.id)"
                                                />
                                            </v-list-item-avatar>
                                            <!-- Server Switch (Selector) Input End -->

                                            <!-- Server Description Box Start -->
                                            <v-list-item-content>
                                                <v-list-item-title>
                                                    {{ server.name }}
                                                </v-list-item-title>
                                                <v-list-item-subtitle>
                                                    <span class="red--text left" v-show="!server.local">
                                                        {{ $t('user.plex.servers.remote') }}
                                                    </span>
                                                    <span class="green--text left" v-show="server.local">
                                                        {{ $t('user.plex.servers.local') }}
                                                    </span>
                                                    <span class="italic right">
                                                        {{ $t('user.plex.servers.version', { version: server.version }) }}
                                                    </span>
                                                    <br />
                                                    <span class="fs-12 left">
                                                        {{ $t('user.plex.servers.last_updated', { date: timestampToLocalDateTime(server.dates.updated) }) }}
                                                    </span>
                                                    <span class="fs-12 right">
                                                        {{ $t('user.plex.servers.ping', { ping: server.ping }) }}
                                                    </span>
                                                </v-list-item-subtitle>
                                            </v-list-item-content>
                                            <!-- Server Description Box End -->
                                        </v-list-item>
                                        <!-- Plex Server List Item End -->
                                    </v-flex>
                                    <!-- [Plex::servers] Block End -->
                                </v-layout>
                            </v-card-text>
                        </v-card>
                    </v-flex>
                </v-slide-x-transition>
                <v-flex xs12 lg4 xl4>
                    <v-card>
                        <v-card-text style="min-height: 265px;">
                            <pre>{{ plexLibraries }}</pre>
                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-flex>
    </v-layout>
</template>
<script>
    import { mapGetters } from 'vuex';

    export default {
        middleware: ['auth'],
        data: () => ({
            // Plex Authentication Related Stuff
            authenticatingWithPlex: false,
            plexAuthenticationWindow: null,
            plexAuthenticationWindowInterval: null,
            plexAuthenticationData: {
                url: null,
                id: null,
                code: null
            },
            selectedPlexServer: null,

            // Plex Media Related Stuff
            fetchingPlexServers: true,
            fetchingPlexLibraries: true,
        }),
        computed: {
            ...mapGetters({
                authenticated: 'account/authenticated',
                isPlexAuthenticated: 'account/plex_authenticated',
                user: 'account/user',
                plexServers: 'plex/servers',
                selectedServer: 'plex/selected_server',
                plexLibraries: 'plex/libraries'
            })
        },
        watch: {
            isPlexAuthenticated(current, previous) {
                if (current && !previous) {
                    this.refreshPlexServersList();
                }
            },
        },
        mounted() {
            this
                .resetPage()
                .joinPrivateEchoChannels();
            if (this.isPlexAuthenticated) {
                if (
                    this.plexServers === null
                    || this.plexServers === undefined
                    || this.plexServers.length === 0
                ) {
                    this.refreshPlexServersList();
                } else {
                    this.fetchingPlexServers = false;
                }
                this.selectedPlexServer = this.selectedServer;
                if (
                    this.selectedPlexServer !== null
                    && this.selectedPlexServer !== undefined
                ) {
                    this.refreshPlexLibrariesList();
                }
            }
        },
        methods: {
            joinPrivateEchoChannels() {
                if (this.authenticated) {
                    this.$echo.private(`account.${this.user.id}`).listen('.plex.continue', (event) => {
                        this.continueSignInWithPlex();
                    });
                }
                return this;
            },
            resetPage() {
                this.authenticatingWithPlex = false;
                this.plexAuthenticationWindow = null;
                this.plexAuthenticationWindowInterval = null;
                this.plexAuthenticationData = {
                    url: null,
                    id: null,
                    code: null
                };
                return this;
            },
            resetPlexAuthenticationData() {
                this.plexAuthenticationData = {
                    url: null,
                    id: null,
                    code: null
                };
            },
            resetPlexAuthenticationWindowInterval() {
                clearInterval(this.plexAuthenticationWindowInterval);
                this.plexAuthenticationWindowInterval = null;
                this.plexAuthenticationWindow = null;
            },
            signInWithPlex() {
                const self = this;
                self.authenticatingWithPlex = true;
                self.$axios.get('account/oauth/plex').then(({ data }) => {
                    self.plexAuthenticationData = data.data;
                    if (process.client) {
                        self.plexAuthenticationWindow = window.open(
                            self.plexAuthenticationData.url,
                            '_blank',
                            [
                                'location=no',
                                'height=500',
                                'width=400'
                            ].join(',')
                        );
                        self.plexAuthenticationWindowInterval = setInterval(() => {
                            if (self.plexAuthenticationWindow.closed) {
                                self.resetPlexAuthenticationWindowInterval();
                                self.authenticatingWithPlex = false;
                            }
                        }, 1000);
                    }
                });
            },
            continueSignInWithPlex() {
                this.resetPlexAuthenticationWindowInterval();
                this.$axios.post('account/oauth/plex', {
                    id: this.plexAuthenticationData.id
                }).then(({ data }) => {
                    this.$store.dispatch('account/setPlexToken', {
                        token: data.data.authentication_token,
                        createCookie: true
                    });
                    this.authenticatingWithPlex = false;
                    this.resetPlexAuthenticationData();
                }).catch(({ response }) => {
                    console.error(response);
                });
            },
            refreshPlexServersList(refresh = false) {
                this.fetchingPlexServers = true;
                this.$store.dispatch('plex/fetchServersList', refresh).then(() => {
                    this.fetchingPlexServers = false;
                });
            },
            refreshPlexLibrariesList() {
                this.fetchingPlexLibraries = true;
                this.$store.dispatch('plex/fetchLibrariesList', this.selectedPlexServer).then(() => {
                    this.fetchingPlexLibraries = false;
                });
            },
            selectNewPreferredServer(serverID) {
                this.selectedPlexServer = serverID;
                this.$cookies.remove('plex_server');
                this.$cookies.set('plex_server', this.selectedPlexServer, {
                    sameSite: true,
                    path: '/'
                });
                this.$store.dispatch('plex/setSelectedServer', this.selectedPlexServer);
                this.refreshPlexLibrariesList();
            }
        }
    };
</script>

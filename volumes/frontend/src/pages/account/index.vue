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
                <v-flex xs12 lg8 xl10>
                    <v-card>
                        <v-card-text style="min-height: 265px;">

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
        data: () => ({
            authenticatingWithPlex: false,
            plexAuthenticationWindow: null,
            plexAuthenticationWindowInterval: null,
            plexAuthenticationData: {
                url: null,
                id: null,
                code: null
            }
        }),
        computed: {
            ...mapGetters({
                authenticated: 'account/authenticated',
                isPlexAuthenticated: 'account/plex_authenticated',
                user: 'account/user',
            })
        },
        mounted() {
            const self = this;
            self.resetPage();
            if (self.authenticated) {
                self.$echo.private(`account.${self.user.id}`).listen('.plex.continue', (event) => {
                    self.continueSignInWithPlex();
                });
            }
        },
        methods: {
            resetPage() {
                this.authenticatingWithPlex = false;
                this.plexAuthenticationData = {
                    url: null,
                    id: null,
                    code: null
                };
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
                }).catch(({ response }) => {
                    console.error(response);
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
            }
        }
    };
</script>

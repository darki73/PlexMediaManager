<template>
    <v-list>
        <v-list-item-group>
            <v-list-item v-if="authenticated" inactive>
                <v-list-item-avatar tile>
                    <v-img :src="user.avatar_url"/>
                </v-list-item-avatar>
                <v-list-item-content>
                    <v-list-item-title class="title">
                        {{ user.username }}
                    </v-list-item-title>
                    <v-list-item-subtitle class="fs-10">
                        {{ user.email }}
                    </v-list-item-subtitle>
                </v-list-item-content>
            </v-list-item>
            <v-list-item v-if="authenticated" inactive>
                <v-list-item-content>
                    <v-btn
                        :color="minimized ? 'red' : 'primary'"
                        @click.stop="logoutUserFromAccount"
                        :outlined="!minimized"
                        :small="minimized"
                        :icon="minimized"
                    >
                        <span v-show="minimized">
                            <v-icon>
                                mdi-logout
                            </v-icon>
                        </span>
                        <span v-show="!minimized">
                            {{ $t('common.log_out') }}
                        </span>
                    </v-btn>
                </v-list-item-content>
            </v-list-item>
            <v-list-item dense v-if="!authenticated" inactive>
                <v-list-item-content>
                    <v-btn
                        color="primary"
                        class="normal-case"
                        nuxt
                        to="/account/login"
                        :small="minimized"
                        :icon="minimized"
                    >
                        <span v-show="minimized">
                            <v-icon>
                                mdi-login
                            </v-icon>
                        </span>
                        <span v-show="!minimized">
                            {{ $t('common.log_in') }}
                        </span>
                    </v-btn>
                </v-list-item-content>
            </v-list-item>
            <v-list-item dense v-if="!authenticated" inactive>
                <v-list-item-content>
                    <v-btn
                        color="green"
                        class="normal-case"
                        nuxt
                        to="/account/create"
                        :small="minimized"
                        :icon="minimized"
                    >
                        <span v-show="minimized">
                            <v-icon>
                                mdi-account-plus
                            </v-icon>
                        </span>
                        <span v-show="!minimized">
                            {{ $t('common.create_account') }}
                        </span>
                    </v-btn>
                </v-list-item-content>
            </v-list-item>
        </v-list-item-group>
    </v-list>
</template>
<script>
    import { mapGetters } from 'vuex';

    export default {
        name: 'dashboard-layout-sidebar-profile',
        data: () => ({
            minimized: false
        }),
        computed: {
            ...mapGetters({
                authenticated: 'account/authenticated',
                user: 'account/user'
            })
        },
        mounted() {
            this.$bus.$on('minimizeDrawer', (minimized) => {
                this.minimized = minimized;
            });
        },
        methods: {
            logoutUserFromAccount() {
                this.$router.push({
                    path: '/'
                });
                setTimeout(() => {
                    this.$store.dispatch('account/logout');
                }, 500);
            }
        }
    };
</script>

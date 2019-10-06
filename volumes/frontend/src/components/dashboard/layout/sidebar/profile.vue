<template>
    <v-list>
        <v-list-item-group>
            <v-list-item inactive>
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
            <v-list-item inactive>
                <v-list-item-content>
                    <v-btn
                        color="primary"
                        @click.stop="logoutUserFromAccount"
                        outlined
                    >
                        {{ $t('common.log_out') }}
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
        computed: {
            ...mapGetters({
                user: 'account/user'
            })
        },
        methods: {
            logoutUserFromAccount() {
                this.$router.push({
                    path: '/dashboard/login'
                });
                setTimeout(() => {
                    this.$store.dispatch('account/logout');
                }, 500);
            }
        }
    };
</script>

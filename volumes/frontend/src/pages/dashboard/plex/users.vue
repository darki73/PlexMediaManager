<template>
    <v-layout row wrap>
        <v-flex xs12>
            <!-- Users List Card + Table Start -->
            <v-flex xs12>
                <v-card>
                    <v-card-text>
                        <v-data-table
                            :headers="headers"
                            :items="users"
                            :items-per-page="10"
                            :loading="loading"
                        >
                            <template v-slot:top>
                                <v-toolbar flat>
                                    <v-toolbar-title>
                                        {{ $t('dashboard.plex.users.users') }}
                                    </v-toolbar-title>
                                    <v-divider
                                        class="mx-4"
                                        inset
                                        vertical
                                    />
                                    <div class="flex-grow-1"/>
                                    <v-btn
                                        @click.stop="syncUsers"
                                        color="green"
                                    >
                                        <v-icon>
                                            mdi-database-refresh
                                        </v-icon>
                                    </v-btn>
                                    <v-btn
                                        class="ml-2"
                                        @click.stop="refreshUsers"
                                        color="primary"
                                    >
                                        <v-icon>
                                            refresh
                                        </v-icon>
                                    </v-btn>
                                </v-toolbar>
                            </template>

                            <template v-slot:body="{ items }">
                                <tbody>
                                <tr
                                    v-for="(item, index) in items"
                                    :key="`requests-table-row-for-item-with-index-${index}`"
                                >
                                    <td align="center">
                                        <v-avatar size="30" tile>
                                            <img :src="item.avatar" :alt="`${item.username} Avatar`">
                                        </v-avatar>
                                    </td>

                                    <td align="center">
                                        {{ item.id }}
                                    </td>

                                    <td align="center">
                                        {{ item.uuid }}
                                    </td>

                                    <td align="center">
                                        {{ hideOnLocal(item.title) }}
                                    </td>

                                    <td align="center">
                                        {{ hideOnLocal(item.username) }}
                                    </td>

                                    <td align="center">
                                        {{ hideOnLocal(item.email) }}
                                    </td>

                                    <td align="center">
                                        <v-icon color="green" v-if="item.admin">
                                            check
                                        </v-icon>
                                        <v-icon color="red" v-else>
                                            close
                                        </v-icon>
                                    </td>

                                    <td align="center">
                                        <v-icon color="green" v-if="item.guest">
                                            check
                                        </v-icon>
                                        <v-icon color="red" v-else>
                                            close
                                        </v-icon>
                                    </td>

                                    <td align="center">
                                        <v-icon color="green" v-if="item.friend">
                                            check
                                        </v-icon>
                                        <v-icon color="red" v-else>
                                            close
                                        </v-icon>
                                    </td>

                                    <td align="center">
                                        <v-icon
                                            small
                                            @click="toggleDeleteUserDialog(item)"
                                        >
                                            delete
                                        </v-icon>
                                    </td>
                                </tr>
                                </tbody>
                            </template>
                        </v-data-table>
                    </v-card-text>
                </v-card>
            </v-flex>
            <!-- Users List Card + Table End -->
        </v-flex>
    </v-layout>
</template>
<script>
    import { mapGetters } from 'vuex';

    export default {
        layout: 'dashboard',
        data() {
            return {
                loading: true,
                headers: [
                    {
                        text: this.$t('dashboard.plex.users.headers.avatar'),
                        align: 'center',
                        sortable: true,
                        value: 'avatar',
                    },
                    {
                        text: this.$t('dashboard.plex.users.headers.id'),
                        align: 'center',
                        sortable: true,
                        value: 'id',
                    },
                    {
                        text: this.$t('dashboard.plex.users.headers.uuid'),
                        align: 'center',
                        value: 'uuid',
                    },
                    {
                        text: this.$t('dashboard.plex.users.headers.title'),
                        align: 'center',
                        value: 'title',
                    },
                    {
                        text: this.$t('dashboard.plex.users.headers.username'),
                        align: 'center',
                        value: 'username',
                    },
                    {
                        text: this.$t('dashboard.plex.users.headers.email'),
                        align: 'center',
                        value: 'email',
                    },
                    {
                        text: this.$t('dashboard.plex.users.headers.admin'),
                        align: 'center',
                        value: 'admin',
                    },
                    {
                        text: this.$t('dashboard.plex.users.headers.guest'),
                        align: 'center',
                        value: 'guest',
                    },
                    {
                        text: this.$t('dashboard.plex.users.headers.friend'),
                        align: 'center',
                        value: 'friend',
                    },
                    {
                        text: this.$t('dashboard.plex.users.headers.actions'),
                        align: 'center',
                        value: 'id',
                    },
                ]
            };
        },
        computed: {
            ...mapGetters({
                users: 'plex/users'
            })
        },
        mounted() {
            if (
                this.users === undefined
                || this.users === null
                || this.users.length === 0
            ) {
                this.fetchPlexUsers();
            } else {
                this.loading = false;
            }
        },
        methods: {
            fetchPlexUsers() {
                this.loading = true;
                this.$store.dispatch('plex/fetchPlexUsers').then(() => {
                    this.loading = false;
                });
            },
            syncUsers() {
                this.loading = true;
                this.$axios.get('dashboard/plex/users-sync').then(() => {
                    this.fetchPlexUsers();
                });
            },
            refreshUsers() {
                this.fetchPlexUsers();
            },
            toggleDeleteUserDialog(item) {

            },
            hideOnLocal(value) {
                if (this.configuration.site.environment === 'local') {
                    return 'hidden';
                }
                return value;
            }
        }
    };
</script>

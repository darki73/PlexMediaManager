<template>
    <v-layout row wrap>
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
                                    {{ $t('dashboard.account.users.users') }}
                                </v-toolbar-title>
                                <v-divider
                                    class="mx-4"
                                    inset
                                    vertical
                                />
                                <div class="flex-grow-1"/>
                                <v-btn
                                    @click.stop="toggleCreateNewUser"
                                    color="green"
                                >
                                    <v-icon>
                                        add
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
                                <td>
                                    {{ item.id }}
                                </td>
                                <td align="center">
                                    <v-avatar size="30" tile>
                                        <img :src="item.avatar_url" :alt="`${item.username} Avatar`">
                                    </v-avatar>
                                </td>
                                <td align="center">
                                    {{ item.username }}
                                </td>
                                <td align="center">
                                    {{ item.email }}
                                </td>
                                <td align="center">
                                    {{ toLocalDateTime(item.email_verified_at) }}
                                </td>
                                <td align="center">
                                    {{ toLocalDateTime(item.created_at) }}
                                </td>
                                <td align="center">
                                    {{ toLocalDateTime(item.updated_at) }}
                                </td>
                                <td align="center">
                                    {{ item.roles[0].name }}
                                </td>
                                <td align="center">
                                    <v-icon
                                        small
                                        class="mr-2"
                                        @click="toggleEditUserDialog(item)"
                                    >
                                        edit
                                    </v-icon>
                                    <v-icon
                                        v-show="user.username !== item.username"
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

        <!-- User Editor Dialog Start -->
        <v-dialog
            v-model="userEditorDialog"
            max-width="500"
            persistent
        >
            <v-card v-if="userEditorDialog">
                <v-card-title>
                    {{ $t('dashboard.account.users.update.title', { username: selectedUser.username }) }}
                </v-card-title>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs12>

                        </v-flex>
                    </v-layout>
                </v-card-text>
                <v-card-actions>
                    <v-spacer/>
                    <v-btn
                        color="red"
                        @click.stop="cancelUserEditor"
                    >
                        {{ $t('common.cancel') }}
                    </v-btn>
                    <v-btn
                        color="primary"
                        @click.stop="updateUser"
                    >
                        {{ $t('common.update') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- User Editor Dialog End -->

        <!-- User Deletion Dialog Start -->
        <v-dialog
            v-model="userDeletionDialog"
            max-width="400"
            persistent
        >
            <v-card v-if="userDeletionDialog">
                <v-card-title>
                    {{ $t('dashboard.account.users.delete.title', { username: selectedUser.username }) }}
                </v-card-title>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs12 text-center>
                            <v-avatar size="120" tile>
                                <img :src="selectedUser.avatar_url" :alt="`${selectedUser.username} Avatar`">
                            </v-avatar>
                        </v-flex>
                        <v-flex xs12 text-center class="headline pt-5">
                            {{ selectedUser.username }} <br />
                            <span class="fs-14">
                                {{ selectedUser.email }}
                            </span>
                        </v-flex>
                        <v-flex xs12 text-center>
                            {{ $t('dashboard.account.users.delete.message') }}
                        </v-flex>
                        <v-flex xs12 text-center class="pt-3 red--text">
                            {{ $t('dashboard.account.users.delete.warning') }}
                        </v-flex>
                    </v-layout>
                </v-card-text>
                <v-card-actions>
                    <v-spacer/>
                    <v-btn
                        color="primary"
                        @click.stop="cancelDeletionDialog"
                    >
                        {{ $t('common.cancel') }}
                    </v-btn>
                    <v-btn
                        color="red"
                        @click.stop="deleteUser"
                        :loading="deletingUser"
                    >
                        {{ $t('common.delete') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- User Deletion Dialog End -->
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
                        text: this.$t('dashboard.account.users.headers.id'),
                        align: 'left',
                        sortable: true,
                        value: 'id',
                    },
                    {
                        text: this.$t('dashboard.account.users.headers.avatar'),
                        align: 'center',
                        value: 'avatar_url',
                    },
                    {
                        text: this.$t('dashboard.account.users.headers.username'),
                        align: 'center',
                        value: 'username',
                    },
                    {
                        text: this.$t('dashboard.account.users.headers.email'),
                        align: 'center',
                        value: 'email',
                    },
                    {
                        text: this.$t('dashboard.account.users.headers.email_verified_at'),
                        align: 'center',
                        value: 'email_verified_at',
                    },
                    {
                        text: this.$t('dashboard.account.users.headers.created_at'),
                        align: 'center',
                        value: 'created_at',
                    },
                    {
                        text: this.$t('dashboard.account.users.headers.updated_at'),
                        align: 'center',
                        value: 'updated_at',
                    },
                    {
                        text: this.$t('dashboard.account.users.headers.role'),
                        align: 'center',
                        value: 'roles',
                    },
                    {
                        text: this.$t('dashboard.account.users.headers.actions'),
                        align: 'center',
                        value: 'id',
                    },
                ],

                defaultUser: {},
                selectedUser: null,

                userEditorDialog: false,
                userDeletionDialog: false,

                updatingUser: false,
                deletingUser: false,
            };
        },
        computed: {
            ...mapGetters({
                user: 'account/user',
                users: 'dashboard/accounts_users'
            })
        },
        beforeDestroy() {
            this.resetPage();
        },
        mounted() {
            this.resetPage();
            if (
                this.users === null
                || this.users === undefined
                || this.users.length === 0
            ) {
                this.refreshUsers();
            } else {
                this.loading = false;
            }
        },
        methods: {
            resetPage() {
                this.loading = true;
                this.defaultUser = {
                    id: null,
                    username: null,
                    email: null,
                    email_verified_at: null,
                    created_at: null,
                    updated_at: null,
                    avatar_url: null,
                    roles: [],
                };
                this.userEditorDialog = false;
                this.userDeletionDialog = false;
                this.updatingUser = false;
                this.deletingUser = false;
            },
            refreshUsers() {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchAccountsUsers').then(() => {
                    this.loading = false;
                });
            },
            toggleEditUserDialog(item) {
                this.selectedUser = Object.assign({}, item);
                setTimeout(() => {
                    this.userEditorDialog = true;
                }, 200);
            },
            cancelUserEditor() {
                this.userEditorDialog = false;
                setTimeout(() => {
                    this.selectedUser = null;
                }, 200);
            },
            updateUser() {

            },
            toggleDeleteUserDialog(item) {
                this.selectedUser = Object.assign({}, item);
                setTimeout(() => {
                    this.userDeletionDialog = true;
                }, 200);
            },
            cancelDeletionDialog() {
                this.userDeletionDialog = false;
                setTimeout(() => {
                    this.selectedUser = null;
                }, 200);
            },
            deleteUser() {
                this.deletingUser = true;
                this.$axios.post('dashboard/accounts/users/delete', {
                    id: this.selectedUser.id,
                    username: this.selectedUser.username,
                    email: this.selectedUser.email
                }).then(() => {
                    this.deletingUser = false;
                    this.refreshUsers();
                    this.cancelDeletionDialog();
                });
            },
            toggleCreateNewUser() {

            }
        }
    };
</script>

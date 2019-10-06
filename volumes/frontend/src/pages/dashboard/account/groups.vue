<template>
    <v-layout row wrap>
        <!-- Groups Card + Table Start -->
        <v-flex xs12>
            <v-card>
                <v-card-text>
                    <v-data-table
                        :headers="headers"
                        :items="Object.values(groups)"
                        :items-per-page="10"
                        :loading="loading"
                    >
                        <template v-slot:top>
                            <v-toolbar flat>
                                <v-toolbar-title>
                                    {{ $t('dashboard.account.groups.groups') }}
                                </v-toolbar-title>
                                <v-divider
                                    class="mx-4"
                                    inset
                                    vertical
                                />
                                <div class="flex-grow-1"/>
                                <v-btn
                                    class="ml-2"
                                    @click.stop="refreshGroups"
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
                                    {{ item.id }}
                                </td>
                                <td align="center">
                                    {{ item.name }}
                                </td>
                                <td align="center">
                                    {{ item.guard_name }}
                                </td>
                                <td align="center">
                                    {{ $t('dashboard.account.groups.permissions_count', { count: item.permissions.length }) }}
                                </td>
                                <td align="center">
                                    <v-icon
                                        small
                                        class="mr-2"
                                        @click="toggleChangeItemDialog(item)"
                                    >
                                        edit
                                    </v-icon>
                                    <v-icon
                                        small
                                        @click="toggleDeleteItemDialog(item)"
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
        <!-- Groups Card + Table End -->

        <!-- Editor Dialog Start -->
        <v-dialog
            v-model="changeItemDialog"
            max-width="750"
            persistent
        >
            <v-card v-if="changeItemDialog">
                <v-card-title>
                    Title
                </v-card-title>
                <v-card-text>
                    <v-layout row wrap>
                        <!-- Group Name Input Start -->
                        <v-flex xs12 lg3>
                            <v-text-field
                                v-model="selectedItem.id"
                                :label="$t('dashboard.account.groups.id.label')"
                                :placeholder="$t('dashboard.account.groups.id.placeholder')"
                                outlined
                                disabled
                            />
                        </v-flex>
                        <!-- Group Name Input End -->

                        <!-- Group Name Input Start -->
                        <v-flex xs12 lg3 offset-lg1>
                            <v-text-field
                                v-model="selectedItem.name"
                                :label="$t('dashboard.account.groups.group.label')"
                                :placeholder="$t('dashboard.account.groups.group.placeholder')"
                                outlined
                                disabled
                            />
                        </v-flex>
                        <!-- Group Name Input End -->

                        <!-- Group Guard Selector Start -->
                        <v-flex xs12 lg3 offset-lg1>
                            <v-select
                                v-model="selectedItem.guard_name"
                                :items="guards"
                                :label="$t('dashboard.account.groups.guard.label')"
                                :placeholder="$t('dashboard.account.groups.guard.placeholder')"
                                outlined
                                disabled
                            />
                        </v-flex>
                        <!-- Group Guard Selector End -->

                        <!-- Permissions Row Start -->
                        <v-flex xs12>
                            <v-layout row wrap>
                                <v-flex xs12 text-center>
                                    SEARCH BAR HERE
                                </v-flex>
                                <v-flex xs12>
                                    <v-layout row wrap>
<!--                                        <v-flex-->
<!--                                            xs12-->
<!--                                            lg4-->
<!--                                            v-for="(permission, index) in selectedItem.permissions"-->
<!--                                            :key="`permission-entry-for-permission-with-index-${index}`"-->
<!--                                        >-->
<!--                                            {{ permission.name }}-->
<!--&lt;!&ndash;                                            <v-switch&ndash;&gt;-->
<!--&lt;!&ndash;                                                v-model="switch1"&ndash;&gt;-->
<!--&lt;!&ndash;                                                :label="`Switch 1: ${switch1.toString()}`"&ndash;&gt;-->
<!--&lt;!&ndash;                                            />&ndash;&gt;-->
<!--                                        </v-flex>-->
                                    </v-layout>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <!-- Permissions Row End -->
                    </v-layout>
                </v-card-text>
                <v-card-actions>
                    <v-spacer/>
                    <v-btn
                        color="red"
                        @click.stop="cancelEditorDialog"
                    >
                        {{ $t('common.cancel') }}
                    </v-btn>
                    <v-btn
                        color="primary"
                        @click.stop="updateGroup"
                    >
                        {{ $t('common.update') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- Editor Dialog End -->
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
                        text: this.$t('dashboard.account.groups.headers.id'),
                        align: 'center',
                        value: 'id',
                    },
                    {
                        text: this.$t('dashboard.account.groups.headers.name'),
                        align: 'center',
                        value: 'name',
                    },
                    {
                        text: this.$t('dashboard.account.groups.headers.guard'),
                        align: 'center',
                        value: 'guard_name',
                    },
                    {
                        text: this.$t('dashboard.account.groups.headers.permissions'),
                        align: 'center',
                        value: 'permissions',
                    },
                    {
                        text: this.$t('dashboard.account.groups.headers.actions'),
                        align: 'center',
                        value: 'id',
                    },
                ],
                guards: [
                    {
                        text: 'API',
                        value: 'api'
                    },
                    {
                        text: 'WEB',
                        value: 'web'
                    }
                ],

                selectedItem: null,
                defaultItem: {
                    id: null,
                    name: null,
                    guard_name: null,
                    permissions: []
                },
                changeItemDialog: false
            };
        },
        computed: {
            ...mapGetters({
                groups: 'dashboard/accounts_groups'
            })
        },
        mounted() {
            if (
                this.groups === null
                || this.groups === undefined
                || this.groups.length === 0
            ) {
                this.refreshGroups();
            } else {
                this.loading = false;
            }
        },
        methods: {
            resetPage() {
                this.loading = true;
                this.selectedItem = Object.assign({}, this.defaultItem);
            },
            refreshGroups() {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchAccountsGroups').then(() => {
                    this.loading = false;
                });
            },
            toggleChangeItemDialog(item) {
                this.selectedItem = Object.assign({}, item);
                setTimeout(() => {
                    this.changeItemDialog = true;
                }, 200);
            },
            cancelEditorDialog() {
                this.changeItemDialog = false;
                setTimeout(() => {
                    this.selectedItem = Object.assign({}, this.defaultItem);
                }, 200);
            },
            updateGroup() {

            },
            toggleDeleteItemDialog(item) {

            },
        }
    };
</script>

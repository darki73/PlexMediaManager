<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-card>
                <v-card-text>
                    <v-data-table
                        :headers="headers"
                        :items="requests"
                        :items-per-page="10"
                        :loading="loading"
                    >
                        <template v-slot:top>
                            <v-toolbar flat>
                                <v-toolbar-title>
                                    {{ translate('table_header') }}
                                </v-toolbar-title>
                                <v-divider
                                    class="mx-4"
                                    inset
                                    vertical
                                />
                                <div class="flex-grow-1"/>
                                <v-btn
                                    @click.stop="refreshRequests"
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
                                        {{ $t('dashboard.requests.types.' + item.request_type) }}
                                    </td>
                                    <td align="center">
                                        {{ item.title }}
                                    </td>
                                    <td align="center">
                                        {{ item.year }}
                                    </td>
                                    <td align="center">
                                        {{ item.user.username }}
                                    </td>
                                    <td align="center">
                                        {{ $t('dashboard.requests.statuses.' + item.status) }}
                                    </td>
                                    <td align="center">
                                        {{ item.created_at }}
                                    </td>
                                    <td align="center">
                                        <v-icon
                                            small
                                            class="mr-2"
                                            @click="changeItem(item)"
                                        >
                                            edit
                                        </v-icon>
                                        <v-icon
                                            small
                                            @click="deleteItem(item)"
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
        <v-dialog
            v-model="showEditDialog"
            max-width="500"
            persistent
        >
            <v-card v-if="showEditDialog">
                <v-card-title class="headline">
                    {{ workingItem.title }} ({{ workingItem.year }})
                </v-card-title>

                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs12 text-center>
                            <!-- TODO: Make image prettier (wrong height now) -->
                            <v-img
                                :src="workingItem.moviedb.poster.w92"
                                :srcset="generateSrcSet(workingItem.moviedb.poster)"
                                :height="150"
                                class="grey darken-4"
                            />
                        </v-flex>
                        <v-flex xs12 text-center class="pt-5">
                            <v-chip
                                class="ml-2"
                                v-for="(genre, index) in workingItem.moviedb.genres"
                                :key="`chip-for-request-with-id-${workingItem.id}-and-chip-index-${index}`"
                            >
                                {{ genre.name }}
                            </v-chip>
                        </v-flex>
                        <v-flex xs12 text-center class="pt-5">
                            {{ createCorrectOverview(workingItem.moviedb.overview) }}
                        </v-flex>
                        <v-flex xs12 class="pt-5">
                            <v-select
                                :label="$t('dashboard.requests.status_label')"
                                v-model="workingItem.status"
                                :items="availableStatuses"
                                item-text="value"
                                item-value="key"
                            />
                        </v-flex>
                    </v-layout>
                </v-card-text>

                <v-card-actions>
                    <div class="flex-grow-1"/>
                    <v-btn
                        outlined
                        color="red"
                        @click="cancelChangeItem"
                    >
                        {{ $t('common.cancel') }}
                    </v-btn>
                    <v-btn
                        outlined
                        color="green"
                        @click="confirmChangeItem"
                    >
                        {{ $t('common.apply') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-layout>
</template>
<script>
    import { mapGetters } from 'vuex';
    import forEach from 'lodash/forEach';

    export default {
        layout: 'dashboard',
        data() {
            return {
                headers: [
                    {
                        text: this.$t('dashboard.requests.headers.id'),
                        align: 'left',
                        sortable: true,
                        value: 'id',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.type'),
                        align: 'center',
                        value: 'request_type',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.title'),
                        align: 'center',
                        value: 'title',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.year'),
                        align: 'center',
                        value: 'year',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.username'),
                        align: 'center',
                        value: 'user.username',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.status'),
                        align: 'center',
                        value: 'status',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.created'),
                        align: 'center',
                        value: 'created_at',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.actions'),
                        align: 'center',
                        value: 'id',
                    },
                ],
                loading: false,
                baseItem: {
                    id: null,
                    user_id: null,
                    request_type: null,
                    title: null,
                    year: null,
                    status: null,
                    created_at: null,
                    updated_at: null,
                    user: {
                        id: null,
                        username: null,
                        email: null,
                        email_verified_at: null,
                        created_at: null,
                        updated_at: null
                    },
                    moviedb: {
                        overview: null,
                        genres: [],
                        poster: {
                            w92: null,
                            w154: null,
                            w185: null,
                            w342: null,
                            w500: null,
                            w780: null,
                            original: null
                        }
                    }
                },
                workingItem: null,
                showEditDialog: false,
                showDeleteDialog: false,
                availableStatuses: [
                    {
                        key: 0,
                        value: this.$t('dashboard.requests.statuses.0')
                    },
                    {
                        key: 1,
                        value: this.$t('dashboard.requests.statuses.1')
                    },
                    {
                        key: 2,
                        value: this.$t('dashboard.requests.statuses.2')
                    },
                    {
                        key: 3,
                        value: this.$t('dashboard.requests.statuses.3')
                    },
                ]
            };
        },
        computed: {
            ...mapGetters({
                requests: 'dashboard/requests'
            })
        },
        mounted() {
            if (
                this.requests === null
                || this.requests === undefined
                || this.requests.length === 0
            ) {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchRequests').then(() => {
                    this.loading = false;
                });
            }
            this.resetWorkingItem();
        },
        methods: {
            translate(key) {
                return this.$t(`dashboard.requests.all.${key}`);
            },
            refreshRequests() {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchRequests').then(() => {
                    this.loading = false;
                });
            },
            createCorrectOverview(overview) {
                const parts = overview.split(' ');
                let text = parts.splice(0,50).join(' ');
                if (parts.length > 50) {
                    text += ' ...';
                }
                return text;
            },
            generateSrcSet(items) {
                const sizes = [];
                forEach(items, (url, size) => {
                    if (size !== 'original') {
                        sizes.push(`${url} ${size.replace('w', '')}w`);
                    }
                });
                return sizes.join(',');
            },
            resetWorkingItem() {
                this.workingItem = Object.assign({}, this.baseItem);
            },
            changeItem(item) {
                this.workingItem = Object.assign({}, item);
                setTimeout(() => {
                    this.showEditDialog = true;
                }, 200);
            },
            deleteItem(item) {
                this.workingItem = Object.assign({}, item);
                setTimeout(() => {
                    this.showDeleteDialog = true;
                }, 200);
            },
            confirmChangeItem() {
                console.warn('[requests::confirmChangeItem] IMPLEMENTATION NEEDED');
            },
            confirmDeleteItem() {
                console.warn('[requests::confirmDeleteItem] IMPLEMENTATION NEEDED!');
            },
            cancelChangeItem() {
                this.showEditDialog = false;
                setTimeout(() => {
                    this.resetWorkingItem();
                }, 200);
            },
            cancelDeleteItem() {
                this.showDeleteDialog = false;
                setTimeout(() => {
                    this.resetWorkingItem();
                }, 200);
            },
        }
    };
</script>

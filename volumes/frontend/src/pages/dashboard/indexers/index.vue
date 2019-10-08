<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-card>
                <v-card-text>
                    <v-data-table
                        :headers="headers"
                        :items="indexers"
                        :items-per-page="10"
                        :loading="loading"
                    >
                        <template v-slot:top>
                            <v-toolbar flat>
                                <v-toolbar-title>
                                    {{ $t('dashboard.indexers.title') }}
                                </v-toolbar-title>
                                <v-divider
                                    class="mx-4"
                                    inset
                                    vertical
                                />
                                <div class="flex-grow-1"/>
                                <v-btn
                                    @click.stop="refreshIndexers"
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
                                    {{ item.name }}
                                </td>
                                <td align="center">
                                    {{ item.class }}
                                </td>
                                <td align="center">
                                    {{ item.items_count }}
                                </td>
                                <td align="center">
                                    <v-btn
                                        color="primary"
                                        small
                                        @click.stop="toggleViewerDialog(item)"
                                    >
                                        {{ $t('dashboard.indexers.view_items') }}
                                    </v-btn>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-data-table>
                </v-card-text>
            </v-card>
        </v-flex>
        <v-dialog
            v-model="viewerDialog"
            max-width="500"
            persistent
        >
            <v-card v-if="viewerDialog">
                <v-card-text class="pt-2">
                    <v-layout row wrap>
                        <!-- Indexer Title Start -->
                        <v-flex xs12 class="headline">
                            {{ $t('dashboard.indexers.indexer', { indexer: selectedItem.name }) }}
                        </v-flex>
                        <!-- Indexer Title End -->

                        <!-- Indexer Class Start -->
                        <v-flex xs12 class="fs-14 italic pb-5">
                            {{ selectedItem.class }}
                        </v-flex>
                        <!-- Indexer Class End -->

                        <v-flex
                            xs12
                            v-for="(item, index) in selectedItem.items"
                            :key="`selected-item-indexer-${selectedItem.name}-item-element-with-index-${index}`"
                        >
                            <v-list-item two-line>
                                <v-list-item-content>
                                    <!-- List Item Title Start -->
                                    <v-list-item-title>
                                        {{ item.title }}
                                        <span class="grey--text text--lighten-1 fs-10" v-if="item.has_torrent">
                                            ({{ $t('dashboard.indexers.series', { series: item.id }) }})
                                        </span>
                                    </v-list-item-title>
                                    <!-- List Item Title End -->

                                    <!-- List Item Subtitle (Has Torrent) Start -->
                                    <v-list-item-subtitle v-if="item.has_torrent">
                                        <span style="float: left;">
                                            {{ createSeasonsStringForItem(item) }}
                                        </span>
                                        <span style="float: right;">
                                            {{ $t('dashboard.indexers.updated', { updated: toLocalDateTime(item.updated_at) }) }}
                                        </span>
                                    </v-list-item-subtitle>
                                    <!-- List Item Subtitle (Has Torrent) End -->

                                    <!-- List Item Subtitle (No Torrent) Start -->
                                    <v-list-item-subtitle v-else>
                                        <span style="float: left;">
                                            {{ $t('dashboard.indexers.series', { series: item.id }) }}
                                        </span>
                                        <span style="float: right;">
                                            {{ $t('dashboard.indexers.updated', { updated: toLocalDateTime(item.updated_at) }) }}
                                        </span>
                                    </v-list-item-subtitle>
                                    <!-- List Item Subtitle (No Torrent) End -->
                                </v-list-item-content>
                            </v-list-item>
                        </v-flex>
                    </v-layout>
                </v-card-text>

                <v-card-actions>
                    <div class="flex-grow-1"/>
                    <v-btn
                        color="primary"
                        @click="cancelViewerDialog"
                    >
                        {{ $t('common.cancel') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-layout>
</template>
<script>
    import { mapGetters } from 'vuex';

    export default {
        layout: 'dashboard',
        data() {
            return {
                loading: true,
                viewerDialog: false,
                selectedItem: {},

                headers: [
                    {
                        text: this.$t('dashboard.indexers.headers.name'),
                        align: 'left',
                        sortable: true,
                        value: 'name',
                    },
                    {
                        text: this.$t('dashboard.indexers.headers.class'),
                        align: 'center',
                        value: 'class',
                    },
                    {
                        text: this.$t('dashboard.indexers.headers.items'),
                        align: 'center',
                        value: 'items_count',
                    },
                    {
                        text: this.$t('dashboard.indexers.headers.actions'),
                        align: 'center',
                        value: 'name',
                    },
                ],
            };
        },
        computed: {
            ...mapGetters({
                indexers: 'dashboard/indexers'
            })
        },
        mounted() {
            this.resetPage();
            if (
                this.indexers === null
                || this.indexers === undefined
                || this.indexers.length === 0
            ) {
                this.refreshIndexers();
            } else {
                this.loading = false;
            }
        },
        methods: {
            resetPage() {
                this.loading = true;
                this.viewerDialog = false;
                this.selectedItem = {};
            },
            refreshIndexers() {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchIndexers').then(() => {
                    this.loading = false;
                });
            },
            toggleViewerDialog(item) {
                this.selectedItem = Object.assign({}, item);
                setTimeout(() => {
                    this.viewerDialog = true;
                }, 200);
            },
            cancelViewerDialog() {
                this.viewerDialog = false;
                setTimeout(() => {
                    this.selectedItem = {};
                }, 200);
            },
            createSeasonsStringForItem(item) {
                const seasons = Object.keys(item.torrent_files);
                return this.$t('dashboard.indexers.season', { season: seasons.join(', ') });
            }
        }
    };
</script>

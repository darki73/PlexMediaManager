<template>
    <v-layout row wrap v-show="indexerExists">
        <v-flex xs12>
            <v-card>
                <v-card-text>
                    <v-data-table
                        :headers="headers"
                        :items="indexer.items"
                        :items-per-page="10"
                        :loading="loading"
                        :search="search"
                    >
                        <template v-slot:top>
                            <v-toolbar flat>
                                <v-toolbar-title>
                                    {{ $t('dashboard.indexers.title_indexer', { indexer: $route.params.indexer.ucfirst() }) }}
                                </v-toolbar-title>
                                <v-divider
                                    class="mx-4"
                                    inset
                                    vertical
                                />
                                <div class="flex-grow-1"/>
                                <v-text-field
                                    v-model="search"
                                    append-icon="search"
                                    :label="$t('search.common.label')"
                                    :placeholder="$t('search.common.placeholder')"
                                    single-line
                                    hide-details
                                    class="mr-3"
                                />
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
                                    {{ item.id }}
                                </td>
                                <td align="center">
                                    {{ item.title }}
                                </td>
                                <td align="center">
                                    {{ item.has_torrent }}
                                </td>
                                <td align="center">
                                    {{ item.created_at }}
                                </td>
                                <td align="center">
                                    {{ item.updated_at }}
                                </td>
                                <td align="center">
                                    <v-btn
                                        color="primary"
                                        small
                                    >
                                        Button
                                    </v-btn>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-data-table>
                </v-card-text>
            </v-card>
        </v-flex>
    </v-layout>
</template>
<script>
    import forEach from 'lodash/forEach';
    import { mapGetters } from 'vuex';

    export default {
        layout: 'dashboard',
        async fetch({ store }) {
            const getter = store.getters['dashboard/indexer'];
            if (
                getter === null
                || getter === undefined
                || getter.length === 0
            ) {
                await store.dispatch('dashboard/fetchIndexers');
            }
        },
        data() {
            return {
                search: '',
                indexerExists: false,
                loading: false,
                indexer: {},
                headers: [
                    {
                        text: this.$t('dashboard.indexers.headers.series_id'),
                        align: 'left',
                        sortable: true,
                        value: 'id',
                    },
                    {
                        text: this.$t('dashboard.indexers.headers.series_title'),
                        align: 'center',
                        value: 'title',
                    },
                    {
                        text: this.$t('dashboard.indexers.headers.series_has_torrent'),
                        align: 'center',
                        value: 'has_torrent',
                    },
                    {
                        text: this.$t('dashboard.indexers.headers.series_created_at'),
                        align: 'center',
                        value: 'created_at',
                    },
                    {
                        text: this.$t('dashboard.indexers.headers.series_updated_at'),
                        align: 'center',
                        value: 'updated_at',
                    },
                    {
                        text: this.$t('dashboard.indexers.headers.actions'),
                        align: 'center',
                        value: 'id',
                    },
                ]
            };
        },
        computed: {
            ...mapGetters({
                indexers: 'dashboard/indexers'
            })
        },
        mounted() {
            forEach(this.indexers, (indexer) => {
                if (indexer.name === this.$route.params.indexer.toLowerCase()) {
                    this.indexerExists = true;
                    this.indexer = indexer;
                }
            });
            if (!this.indexerExists) {
                this.$router.push('/dashboard/indexers');
            }
        },
        methods: {
            refreshIndexers() {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchIndexers').then(() => {
                    this.loading = false;
                });
            },
        }
    };
</script>

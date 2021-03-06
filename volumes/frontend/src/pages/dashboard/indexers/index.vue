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
                                        nuxt
                                        :to="`/dashboard/indexers/${item.name}`"
                                    >
                                        {{ $t('dashboard.indexers.view_items') }}
                                        <v-icon right>
                                            keyboard_arrow_right
                                        </v-icon>
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
    import { mapGetters } from 'vuex';

    export default {
        layout: 'dashboard',
        data() {
            return {
                loading: true,

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
            },
            refreshIndexers() {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchIndexers').then(() => {
                    this.loading = false;
                });
            },
            createSeasonsStringForItem(item) {
                const seasons = Object.keys(item.torrent_files);
                return this.$t('dashboard.indexers.season', { season: seasons.join(', ') });
            }
        }
    };
</script>

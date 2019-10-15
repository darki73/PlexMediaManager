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
                                        @click.stop="toggleItemDialog(item)"
                                    >
                                        <v-icon>
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
        <v-dialog
            persistent
            max-width="700"
            v-model="showItemDialog"
        >
            <v-card v-if="showItemDialog">
                <v-card-text>
                    <v-layout row wrap>
                        <!-- Item Dialog Loading Series Spinner Start -->
                        <v-flex xs12 text-center class="pt-5 pb-4" v-if="loadingSeriesInformation">
                            <v-progress-circular
                                :size="70"
                                color="primary"
                                indeterminate
                            />
                        </v-flex>
                        <!-- Item Dialog Loading Series Spinner End -->

                        <!-- Item Dialog Loading Series Text Start -->
                        <v-flex xs12 text-center v-if="loadingSeriesInformation">
                            {{ $t('dashboard.indexers.loading_series') }}
                        </v-flex>
                        <!-- Item Dialog Loading Series Text End -->

                        <!-- Item Dialog Series Information Start -->
                        <v-flex xs12 v-if="!loadingSeriesInformation">
                            <v-layout row wrap>
                                <!-- Series Image Overlay + Details Start -->
                                <v-flex xs12>
                                    <v-img
                                        class="white--text align-end"
                                        :src="selectedItem.series.backdrop.w300"
                                        :srcset="generateSrcSet(selectedItem.series.backdrop)"
                                        :height="200"
                                        gradient="to top right, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)"

                                    >
                                        <v-container>
                                            <v-layout row wrap class="pa-3">
                                                <v-flex xs12 class="fs-24 shadow-10">
                                                    {{ selectedItem.series.original_title }}
                                                </v-flex>
                                                <v-flex xs12 class="shadow-6 pt-2">
                                                    {{ $tc('dashboard.indexers.preview.seasons', selectedItem.series.seasons.length, { seasons: selectedItem.series.seasons.length }) }} |
                                                    {{ $tc('dashboard.indexers.preview.episodes', selectedItem.series.episodes_count, { episodes: selectedItem.series.episodes_count }) }}
                                                </v-flex>
                                                <v-flex xs12 class="shadow-6">
                                                    {{ $t('dashboard.indexers.preview.released', { date: selectedItem.series.release_date }) }}
                                                </v-flex>
                                            </v-layout>
                                        </v-container>
                                    </v-img>
                                </v-flex>
                                <!-- Series Image Overlay + Details End -->

                                <!-- Series Episodes and Seasons Start -->
                                <v-flex xs12 class="pa-5">
                                    <v-expansion-panels accordion>
                                        <v-expansion-panel
                                            v-for="(season, index) in selectedItem.series.seasons"
                                            :key="`season-entry-for-seasons-with-index-${index}`"
                                        >
                                            <v-expansion-panel-header>
                                                {{ season.name }}
                                                <v-spacer/>
                                                <small class="ml-4">
                                                    {{ $t('dashboard.indexers.preview.downloaded', {
                                                    downloaded: season.episodes_downloaded,
                                                    total: season.episodes_count
                                                    }) }}
                                                </small>
                                            </v-expansion-panel-header>
                                            <v-expansion-panel-content>
                                                <v-layout row wrap>

                                                    <!-- Season Download Switch Start -->
                                                    <v-flex xs12 class="pb-0">
                                                        <v-layout row wrap class="pl-4 pr-4" justify-center align-center>
                                                            <v-flex xs10>
                                                                {{ $t('dashboard.indexers.preview.switch.text') }}
                                                            </v-flex>
                                                            <v-flex xs2>
                                                                <v-switch
                                                                    style="display: flex; justify-content: center;"
                                                                    color="primary"
                                                                    v-model="selectedItem.excludes[season.season_number]"
                                                                />
                                                            </v-flex>
                                                        </v-layout>
                                                    </v-flex>
                                                    <!-- Season Download Switch End -->

                                                    <!-- Season Torrent File Start -->
                                                    <v-flex xs12 class="pt-0" v-if="selectedItem.torrent_files.hasOwnProperty(season.season_number)">
                                                        <v-text-field
                                                            v-model="selectedItem.torrent_files[season.season_number].torrent_file"
                                                            :label="$t('dashboard.indexers.preview.torrent.title')"
                                                            :placeholder="$t('dashboard.indexers.preview.torrent.placeholder')"
                                                        />
                                                    </v-flex>
                                                    <!-- Season Torrent File End -->

                                                    <!-- Season Overview Start -->
                                                    <v-flex xs12 text-center class="pb-5">
                                                        {{ season.overview }}
                                                    </v-flex>
                                                    <!-- Season Overview End -->

                                                    <v-flex
                                                        xs12
                                                        v-for="(episode, episodeIndex) in season.episodes"
                                                        :key="`season-episode-entry-for-season-${index}-and-episode-${episodeIndex}`"
                                                        class="mb-2"
                                                    >
                                                        <v-layout row wrap class="pl-4 pr-4">
                                                            <v-flex xs10>
                                                                {{ $t('dashboard.indexers.preview.episode', {
                                                                number: episode.episode_number,
                                                                title: episode.title
                                                                }) }}
                                                            </v-flex>
                                                            <v-flex xs2>
                                                                <v-btn
                                                                    v-show="canBeDownloaded(episode)"
                                                                    small
                                                                    color="primary"
                                                                    @click.stop="sendDownloadRequest(episode.series_id, episode.season_number, episode.episode_number)"
                                                                >
                                                                    {{ $t('dashboard.indexers.preview.status.download') }}
                                                                </v-btn>
                                                                <v-btn
                                                                    v-show="episode.downloaded"
                                                                    small
                                                                    :disabled="episode.downloaded"
                                                                >
                                                                    {{ $t('dashboard.indexers.preview.status.downloaded') }}
                                                                </v-btn>
                                                            </v-flex>
                                                        </v-layout>
                                                    </v-flex>
                                                </v-layout>
                                            </v-expansion-panel-content>
                                        </v-expansion-panel>
                                    </v-expansion-panels>
                                </v-flex>
                                <!-- Series Episodes and Seasons End -->
                            </v-layout>
                        </v-flex>
                        <!-- Item Dialog Series Information End -->
                    </v-layout>
                </v-card-text>
                <v-card-actions v-show="!loadingSeriesInformation">
                    <v-spacer/>
                    <v-btn
                        color="red"
                        @click.stop="closeItemDialog"
                        min-width="100"
                    >
                        {{ $t('common.close')}}
                    </v-btn>
                    <v-btn
                        color="green"
                        @click.stop="updateItem"
                        min-width="100"
                    >
                        {{ $t('common.update') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-layout>
</template>
<script>
    import isEqual from 'lodash/isEqual';
    import forEach from 'lodash/forEach';
    import cloneDeep from 'lodash/cloneDeep';
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
                ],

                showItemDialog: false,
                loadingSeriesInformation: false,
                selectedItem: {},
                defaultItem: {
                    id: null,
                    title: null,
                    has_torrent: false,
                    series: {
                        id: null,
                        title: null,
                        original_title: null,
                        local_title: null,
                        original_language: null,
                        languages: [],
                        overview: null,
                        homepage: null,
                        runtime: null,
                        status: null,
                        episodes_count: null,
                        seasons_count: null,
                        release_date: null,
                        last_air_date: null,
                        origin_country: null,
                        in_production: false,
                        backdrop: {
                            w300: null,
                            w780: null,
                            w1280: null,
                            original: null,
                        },
                        poster: {
                            w92: null,
                            w154: null,
                            w185: null,
                            w342: null,
                            w500: null,
                            w780: null,
                            original: null
                        },
                        seasons: []
                    },
                    torrent_files: [],
                    created_at: null,
                    updated_at: null
                }
            };
        },
        computed: {
            ...mapGetters({
                indexers: 'dashboard/indexers'
            })
        },
        mounted() {
            this.updateTheSelectedIndexerValue();
            if (!this.indexerExists) {
                this.$router.push('/dashboard/indexers');
            }
        },
        methods: {
            updateTheSelectedIndexerValue() {
                forEach(this.indexers, (indexer) => {
                    if (indexer.name === this.$route.params.indexer.toLowerCase()) {
                        this.indexerExists = true;
                        this.indexer = indexer;
                    }
                });
            },
            refreshIndexers() {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchIndexers').then(() => {
                    this.loading = false;
                    this.updateTheSelectedIndexerValue();
                });
            },
            toggleItemDialog(item) {
                this.selectedItem = cloneDeep(item);
                this.loadingSeriesInformation = true;
                this.$axios.get(`series/${this.selectedItem.id}`).then(({ data }) => {
                    this.selectedItem.series = data.data;
                    this.$axios.get(`series/${this.selectedItem.id}/seasons`).then(({ data }) => {
                        this.selectedItem.series.seasons = data.data;
                        this.loadingSeriesInformation = false;
                    });
                });
                setTimeout(() => {
                    this.showItemDialog = true;
                }, 200);
            },
            closeItemDialog() {
                this.showItemDialog = false;
                this.loadingSeriesInformation = false;
                setTimeout(() => {
                    this.selectedItem = Object.assign({}, this.defaultItem);
                }, 200);
            },
            updateItem() {
                this.updateItemTorrentsList().then(() => {
                    this.updateItemExclusionList();
                    this.closeItemDialog();
                    // console.warn('Now we are updating exclusion list');
                });
            },
            async updateItemTorrentsList() {
                if (this.selectedItem.has_torrent) {
                    const localTorrentFiles = this.selectedItem.torrent_files;
                    let originalTorrentFiles = null;
                    forEach(this.indexer.items, (item) => {
                        if (item.id === this.selectedItem.id) {
                            originalTorrentFiles = item.torrent_files;
                        }
                    });

                    if (originalTorrentFiles !== null && !isEqual(originalTorrentFiles, localTorrentFiles)) {
                        return this.$axios.post('dashboard/indexers/update-torrents-list', {
                            id: this.selectedItem.id,
                            torrents: localTorrentFiles
                        }).then(({ data }) => {
                            setTimeout(() => {
                                this.refreshIndexers();
                            }, 200);
                            return true;
                        });
                    }
                }
                return false;
            },
            updateItemExclusionList() {
                const localExcludes = this.selectedItem.excludes;
                let originalExcludes = null;
                forEach(this.indexer.items, (item) => {
                    if (item.id === this.selectedItem.id) {
                        originalExcludes = item.excludes;
                    }
                });
                if (originalExcludes !== null && !isEqual(originalExcludes, localExcludes)) {
                    this.$axios.post('dashboard/indexers/update-exclusion-list', {
                        id: this.selectedItem.id,
                        excludes: localExcludes
                    }).then(() => {
                        this.closeItemDialog();
                        setTimeout(() => {
                            this.refreshIndexers();
                        }, 300);
                    });
                } else {
                    this.closeItemDialog();
                }
            },
            sendDownloadRequest(seriesID, seasonNumber, episodeNumber) {
                // TODO: Implement manuall download request feature
                console.warn({
                    series_id: seriesID,
                    season_number: seasonNumber,
                    episode_number: episodeNumber
                });
            },
            canBeDownloaded(episode) {
                const currentTime = this.$moment().format('YYYY-MM-DD');
                return !episode.downloaded && this.$moment(episode.release_date).isSameOrBefore(currentTime, 'day');
            }
        }
    };
</script>

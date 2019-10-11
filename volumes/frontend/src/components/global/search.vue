<template>
    <v-layout row wrap>
        <v-flex xs12  lg8 offset-lg2>
            <v-card>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-layout row wrap>
                                <!-- Type Selector Start -->
                                <v-flex xs12 lg3>
                                    <v-select
                                        v-model="form.type"
                                        :items="types"
                                        item-text="text"
                                        item-value="value"
                                        :label="$t('search.type_title')"
                                        :placeholder="$t('search.type_placeholder')"
                                    />
                                </v-flex>
                                <!-- Type Selector End -->

                                <!-- Search Input Start -->
                                <v-flex xs12 lg6>
                                    <v-text-field
                                        v-model="form.query"
                                        :disabled="loadingProviders"
                                        :label="$t('search.query_title')"
                                        :placeholder="$t('search.query_placeholder')"
                                        @keyup="performSearch"
                                    />
                                </v-flex>
                                <!-- Search Input End -->

                                <!-- Filter Selector Start -->
                                <v-flex xs12 lg3>
                                    <v-select
                                        v-model="selectedFilter"
                                        :items="availableFilters"
                                        item-text="text"
                                        item-value="value"
                                        :label="$t('search.filter_title')"
                                        :placeholder="$t('search.filter_placeholder')"
                                        :disabled="results.length === 0"
                                    />
                                </v-flex>
                                <!-- Filter Selector End -->
                            </v-layout>
                        </v-flex>
                        <v-flex xs12>
                            <v-layout row wrap>
                                <!-- Searching Spinner and Text Start -->
                                <v-flex
                                    xs12
                                    text-center
                                    v-show="searching"
                                >
                                    <v-layout row wrap>
                                        <v-flex xs12>
                                            <v-progress-circular
                                                :size="70"
                                                color="primary"
                                                indeterminate
                                            />
                                        </v-flex>
                                        <v-flex xs12 class="pt-4">
                                            {{ $t('search.searching') }}
                                        </v-flex>
                                    </v-layout>
                                </v-flex>
                                <!-- Searching Spinner and Text End -->

                                <!-- No search results Start -->
                                <v-flex
                                    xs12
                                    v-if="finishedSearching && filteredResults.length === 0"
                                >
                                    <v-layout row wrap text-center>
                                        <v-flex xs12>
                                            <v-icon size="70">
                                                mdi-emoticon-sad-outline
                                            </v-icon>
                                        </v-flex>
                                        <v-flex xs12 class="pt-4">
                                            {{ $t('search.no_results') }}
                                        </v-flex>
                                    </v-layout>
                                </v-flex>
                                <!-- No search results End -->

                                <v-flex
                                    xs12
                                    lg4
                                    v-for="(item, index) in filteredResults"
                                    :key="`flex-entry-for-item-with-index-${index}`"
                                    class="pa-2"
                                >
                                    <v-card style="background-color: #272727!important;">
                                        <v-card-text class="pa-0">
                                            <v-layout row wrap>
                                                <v-flex xs12 lg4>
                                                    <v-img
                                                        contain
                                                        :src="item.poster.w92"
                                                        :srcset="generateSrcSet(item.poster)"
                                                        class="grey darken-4"
                                                        max-height="220"
                                                    />
                                                </v-flex>
                                                <v-flex xs12 lg8 class="pa-2">
                                                    <v-layout row wrap>
                                                        <!-- Item Rating + Title + Date Start -->
                                                        <v-flex xs12>
                                                            <v-layout row wrap>
                                                                <!-- Item Rating Start -->
                                                                <v-flex xs12 lg2 text-center justify-center>
                                                                    <v-progress-circular
                                                                        :rotate="-90"
                                                                        :size="40"
                                                                        :width="4"
                                                                        :value="item.vote_average * 10"
                                                                        :color="getRatingColor(item.vote_average * 10)"
                                                                    >
                                                                        {{ item.vote_average * 10  }}
                                                                    </v-progress-circular>
                                                                </v-flex>
                                                                <!-- Item Rating End -->

                                                                <!-- Item Title and Release Date Start -->
                                                                <v-flex xs12 lg10>
                                                                    <v-layout row wrap>
                                                                        <!-- Item Title Start -->
                                                                        <v-flex xs12 class="bold fs-16 pb-0 mb-0">
                                                                            {{ getItemTitle(item) }}
                                                                        </v-flex>
                                                                        <!-- Item Title End -->

                                                                        <!-- Item Release Date Start -->
                                                                        <v-flex xs12>
                                                                            {{ $moment(getItemReleaseDate(item)).format('MMMM D, YYYY') }}
                                                                        </v-flex>
                                                                        <!-- Item Release Date End -->
                                                                    </v-layout>
                                                                </v-flex>
                                                                <!-- Item Title and Release Date End -->
                                                            </v-layout>
                                                        </v-flex>
                                                        <!-- Item Rating + Title + Date End -->

                                                        <v-flex xs12>
                                                            {{ trimItemOverview(item.overview, 30) }}
                                                        </v-flex>
                                                    </v-layout>
                                                </v-flex>
                                                <v-flex xs12>
                                                    <v-btn
                                                        block
                                                        color="primary"
                                                        v-show="!item.exists"
                                                        @click.stop="showOrderItemDialog(item)"
                                                        :disabled="item.requested"
                                                    >
                                                        <span v-if="!item.requested">
                                                            {{ $t('search.order') }}
                                                        </span>
                                                        <span v-else>
                                                            <span v-if="item.request_status !== 2">
                                                                {{ $t('search.requested') }}
                                                            </span>
                                                            <span v-else>
                                                                {{ $t('search.request_denied') }}
                                                            </span>
                                                        </span>
                                                    </v-btn>
                                                    <v-btn
                                                        block
                                                        color="green"
                                                        v-show="item.exists && item.hasOwnProperty('watch')"
                                                        :href="item.watch"
                                                        target="_blank"
                                                    >
                                                        {{ $t('search.watch') }}
                                                    </v-btn>
                                                </v-flex>
                                            </v-layout>
                                        </v-card-text>
                                    </v-card>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>
        <v-dialog
            v-model="showOrderDialog"
            persistent
            max-width="500"
        >
            <v-card v-if="showOrderDialog">
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <!-- TODO: Make image prettier (wrong height now) -->
                            <v-img
                                :src="selectedItem.poster.w92"
                                :srcset="generateSrcSet(selectedItem.poster)"
                                max-height="250"
                                contain
                            />
                        </v-flex>
                        <!-- Item Localized Name Start -->
                        <v-flex xs12 class="pb-0 headline text-center">
                            {{ getItemTitle(selectedItem) }}
                        </v-flex>
                        <!-- Item Localized Name End -->

                        <!-- Item Original Name Start -->
                        <v-flex xs12 class="pt-0 pb-0 italic fs-12 text--grey text-center">
                            {{ getItemOriginalTitle(selectedItem) }}
                        </v-flex>
                        <!-- Item Original Name End -->

                        <v-flex xs12 text-center>
                            {{ selectedItem.overview }}
                        </v-flex>
                    </v-layout>
                </v-card-text>
                <v-card-actions>
                    <v-spacer/>
                    <v-btn
                        @click="cancelOrderItemDialog"
                        color="red"
                    >
                        {{ $t('common.cancel') }}
                    </v-btn>
                    <v-btn
                        @click="orderItem"
                        color="green"
                        :loading="ordering"
                    >
                        {{ $t('search.order') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-layout>
</template>
<script>
    import forEach from 'lodash/forEach';
    import debounce from 'lodash/debounce';
    import { mapGetters } from 'vuex';

    export default {
        name: 'global-search',
        data() {
            return {
                loadingProviders: false,
                types: [
                    {
                        text: this.$t('search.categories.all'),
                        value: 'any'
                    },
                    {
                        text: this.$t('search.categories.movies'),
                        value: 'movie'
                    },
                    {
                        text: this.$t('search.categories.series'),
                        value: 'tv'
                    }
                ],
                form: {
                    query: '',
                    type: 'any',
                },

                searching: false,
                finishedSearching: false,
                results: [],
                filteredResults: [],
                selectedFilter: null,
                availableFilters: [],
                showOrderDialog: false,
                selectedItem: {},
                ordering: false
            };
        },
        computed: {
            ...mapGetters({
                authenticated: 'account/authenticated',
                isPlexAuthenticated: 'account/plex_authenticated',
                user: 'account/user',
                plexServers: 'plex/servers',
                selectedServer: 'plex/selected_server',
                plexLibraries: 'plex/libraries'
            })
        },
        watch: {
            'form.query' (current, previous) {
                if (
                    (current === null
                        || current === undefined
                        || current.length < 3)
                    && this.results.length !== 0
                ) {
                    this.resetPage();
                }
            },
            selectedFilter(current, previous) {
                if (current === null) {
                    this.filteredResults = Object.assign({}, this.results);
                } else {
                    this.filteredResults = [];
                    forEach(this.results, (item, index) => {
                        if (item.media_type === current) {
                            this.filteredResults.push(item);
                        }
                    });
                }
            },
            searching(current, previous) {
                if (previous === true && current === false) {
                    this.finishedSearching = true;
                } else {
                    this.finishedSearching = false;
                }
            }
        },
        beforeDestroy() {
            this.resetPage();
        },
        mounted() {
            this.resetPage();
        },
        methods: {
            resetPage() {
                this.searching = false;
                this.results = [];
                this.filteredResults = [];
                this.form = {
                    query: '',
                    type: 'any'
                };
                this.availableFilters = [];
            },
            performSearch: debounce(function() {
                if (this.form.query.length > 2) {
                    this.sendSearchQuery();
                }
            }, 1000),
            sendSearchQuery() {
                this.searching = true;
                if (!this.isPlexAuthenticated) {
                    this.$axios.post('search/remote', this.form).then(({ data }) => {
                        this.results = data.data;
                        this.filteredResults = data.data;
                        this.searching = false;
                        this.updateAvailableFilters();
                    });
                } else {
                    this.$axios.post('search/remote-plex', {
                        server: this.selectedServer,
                        category: Object.keys(this.plexLibraries).join(','),
                        ...this.form
                    }).then(({ data }) => {
                        this.results = data.data;
                        this.filteredResults = data.data;
                        this.searching = false;
                        this.updateAvailableFilters();
                    });
                }
            },
            updateAvailableFilters() {
                this.availableFilters = [
                    {
                        text: this.$t('search.categories.all'),
                        value: null
                    }
                ];
                this.selectedFilter = null;
                forEach(this.results, (item, index) => {
                    this.availableFilters.uniquePush({
                        text: this.$t(`search.categories.${item.media_type}`),
                        value: item.media_type
                    });
                });
            },
            getItemTitle(item) {
                return item.hasOwnProperty('name') ? item.name : item.title;
            },
            getItemOriginalTitle(item) {
                return item.hasOwnProperty('original_name') ? item.original_name : item.original_title;
            },
            getItemReleaseDate(item) {
                return item.hasOwnProperty('first_air_date') ? item.first_air_date : item.release_date;
            },
            getItemGenres(item) {
                return item.genre.map((item, index) => item.name).join(', ');
            },
            showOrderItemDialog(item) {
                this.showOrderDialog = true;
                this.selectedItem = Object.assign({}, item);
            },
            cancelOrderItemDialog() {
                this.showOrderDialog = false;
                setTimeout(() => {
                    this.selectedItem = {};
                }, 200);
            },
            orderItem() {
                this.ordering = true;
                this.$axios.post('requests/create', {
                    title: this.getItemOriginalTitle(this.selectedItem),
                    released: this.getItemReleaseDate(this.selectedItem),
                    type: this.selectedItem.media_type
                }).then(({ data }) => {
                    this.ordering = false;
                    this.cancelOrderItemDialog();
                    this.sendSearchQuery();
                });
            },
            trimItemOverview(item, count = 35) {
                const items = item.split(' ');
                let result = items.slice(0, count).join(' ');
                if (items.length > count) {
                    result += '...';
                }
                return result;
            },
            getRatingColor(rating) {
                let color = 'green lighten-2';

                if (rating < 70 && rating >= 40) {
                    color = 'yellow darken-2';
                } else if (rating < 40) {
                    color = 'red';
                }
                return color;
            }
        }
    };
</script>

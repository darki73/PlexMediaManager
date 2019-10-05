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
                                <v-flex
                                    xs12
                                    lg4
                                    v-for="(item, index) in filteredResults"
                                    :key="`flex-entry-for-item-with-index-${index}`"
                                >
                                    <v-card style="background-color: #272727!important;">
                                        <v-card-text>
                                            <v-layout row wrap>
                                                <v-flex xs12>
                                                    <!-- TODO: Make image prettier (wrong height now) -->
                                                    <v-img
                                                        :src="item.poster.w92"
                                                        :srcset="generateSrcSet(item.poster)"
                                                        class="grey darken-4"
                                                        max-height="150"
                                                    />
                                                </v-flex>
                                                <v-flex xs12>
                                                    <v-layout row wrap>
                                                        <!-- Item Localized Name Start -->
                                                        <v-flex xs12 class="pb-0 headline">
                                                            {{ getItemTitle(item) }}
                                                        </v-flex>
                                                        <!-- Item Localized Name End -->

                                                        <!-- Item Original Name Start -->
                                                        <v-flex xs12 class="pt-0 pb-0 italic fs-12 text--grey">
                                                            {{ getItemOriginalTitle(item) }}
                                                        </v-flex>
                                                        <!-- Item Original Name End -->

                                                        <!-- Item Genres Start -->
                                                        <v-flex xs12>
                                                            <strong>{{ $t('search.genres') }}:</strong> {{ getItemGenres(item) }}
                                                        </v-flex>
                                                        <!-- Item Genres End -->

                                                        <!-- Item First Aired | Release Date Start -->
                                                        <v-flex xs12>
                                                            <strong>{{ $t('search.release_date') }}:</strong> {{ getItemReleaseDate(item) }}
                                                        </v-flex>
                                                        <!-- Item First Aired | Release Date End -->

                                                        <!-- Item Rating Start -->
                                                        <v-flex xs12>
                                                            <strong>{{ $t('search.average_rating') }}:</strong> {{ item.vote_average }} / 10
                                                        </v-flex>
                                                        <!-- Item Rating End -->
                                                    </v-layout>
                                                </v-flex>
                                            </v-layout>
                                        </v-card-text>
                                        <v-card-actions>
                                            <v-layout row wrap>
                                                <v-flex xs10>
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
                                                        v-show="item.exists"
                                                        @click.stop="watchItem(item)"
                                                    >
                                                        {{ $t('search.watch') }}
                                                    </v-btn>
                                                </v-flex>
                                                <v-flex xs2 text-right>
                                                    <v-btn
                                                        v-if="item.overview.length > 0"
                                                        icon
                                                        @click="item.show = !item.show"
                                                    >
                                                        <v-icon>{{ item.show ? 'keyboard_arrow_up' : 'keyboard_arrow_down' }}</v-icon>
                                                    </v-btn>
                                                </v-flex>
                                            </v-layout>
                                        </v-card-actions>
                                        <v-expand-transition>
                                            <div v-show="item.show">
                                                <v-card-text>
                                                    {{ item.overview }}
                                                </v-card-text>
                                            </div>
                                        </v-expand-transition>
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

    export default {
        layout: 'dashboard',
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
                results: [],
                filteredResults: [],
                selectedFilter: null,
                availableFilters: [],
                showOrderDialog: false,
                selectedItem: {},
                ordering: false
            };
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
                this.$axios.post('search/remote', this.form).then(({ data }) => {
                    this.results = data.data;
                    this.filteredResults = data.data;
                    this.searching = false;
                    this.updateAvailableFilters();
                });
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
            watchItem(item) {
                console.warn('[search::watchItem] Not Yet Implemented. Should redirect to Plex.', item);
            },
        }
    };
</script>

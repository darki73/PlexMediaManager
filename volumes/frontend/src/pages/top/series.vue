<template>
    <v-layout row wrap>
        <v-flex xs12 lg8 offset-lg2>
            <v-layout row wrap>
                <v-flex
                    xs12
                    lg4
                    v-for="(series, index) in items"
                    :key="`series-row-for-series-with-index-${index}`"
                    v-show="series.poster_path !== null"
                    class="pa-2"
                >
                    <v-card style="background-color: #272727!important;">
                        <v-card-text class="pa-0">
                            <v-layout row wrap>
                                <v-flex xs12 lg4>
                                    <v-img
                                        contain
                                        :src="getSeriesImage(series)"
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
                                                        :value="series.vote_average * 10"
                                                        :color="getRatingColor(series.vote_average * 10)"
                                                    >
                                                        {{ series.vote_average * 10  }}
                                                    </v-progress-circular>
                                                </v-flex>
                                                <!-- Item Rating End -->

                                                <!-- Item Title and Release Date Start -->
                                                <v-flex xs12 lg10>
                                                    <v-layout row wrap>
                                                        <!-- Item Title Start -->
                                                        <v-flex xs12 class="bold fs-16 pb-0 mb-0">
                                                            {{ series.name }}
                                                        </v-flex>
                                                        <!-- Item Title End -->

                                                        <!-- Item Release Date Start -->
                                                        <v-flex xs12>
                                                            {{ $moment(getItemReleaseDate(series)).format('MMMM D, YYYY') }}
                                                        </v-flex>
                                                        <!-- Item Release Date End -->
                                                    </v-layout>
                                                </v-flex>
                                                <!-- Item Title and Release Date End -->
                                            </v-layout>
                                        </v-flex>
                                        <!-- Item Rating + Title + Date End -->

                                        <v-flex xs12>
                                            {{ trimItemOverview(series.overview, 30) }}
                                        </v-flex>
                                    </v-layout>
                                </v-flex>
                            </v-layout>
                        </v-card-text>
                    </v-card>
                </v-flex>
                <v-flex
                    xs12
                    lg4
                    class="pa-2"
                    v-show="loading"
                    v-for="i in 20"
                    :key="`skeleton-loader-with-index=${i}`"
                >
                    <v-skeleton-loader type="image" min-height="230" max-height="230"/>
                </v-flex>
            </v-layout>
        </v-flex>
    </v-layout>
</template>
<script>
    import forEach from 'lodash/forEach';

    export default {
        data: () => ({
            loading: true,
            currentPage: 1,
            totalPages: 1,
            language: 'en',
            items: [],
            bottom: false,
        }),
        watch: {
            bottom(current, previous) {
                if (current) {
                    this.currentPage++;
                    this.fetchTopPicksList();
                }
            }
        },
        mounted() {
            this.language = this.$i18n.locale;
            if (process.client) {
                window.addEventListener('scroll', () => {
                    this.bottom = this.bottomVisible();
                });
            }
            this.fetchTopPicksList();
        },
        methods: {
            bottomVisible() {
                const scrollY = window.scrollY;
                const visible = document.documentElement.clientHeight;
                const pageHeight = document.documentElement.scrollHeight;
                const bottomOfPage = visible + scrollY >= pageHeight;
                return bottomOfPage || pageHeight < visible;
            },
            fetchTopPicksList() {
                this.loading = true;
                this.$axios.post('series/top-picks', {
                    page: this.currentPage,
                    language: this.language
                }).then(({ data }) => {
                    this.totalPages = data.data.total_pages;
                    forEach(data.data.results, (result) => {
                        this.items.push(result);
                    });
                    this.loading = false;
                });
            },
            getSeriesImage(series) {
                return `https://image.tmdb.org/t/p/w342${series.poster_path}`;
            },
            getRatingColor(rating) {
                let color = 'green lighten-2';

                if (rating < 70 && rating >= 40) {
                    color = 'yellow darken-2';
                } else if (rating < 40) {
                    color = 'red';
                }
                return color;
            },
            trimItemOverview(item, count = 35) {
                const items = item.split(' ');
                let result = items.slice(0, count).join(' ');
                if (items.length > count) {
                    result += '...';
                }
                return result;
            },
            getItemReleaseDate(item) {
                return item.hasOwnProperty('first_air_date') ? item.first_air_date : item.release_date;
            },
        }
    };
</script>

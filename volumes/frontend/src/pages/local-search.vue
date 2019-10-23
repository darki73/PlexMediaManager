<template>
    <v-layout row wrap>
        <!-- Top Bar Start -->
        <v-flex
            xs12
            lg10 offset-lg1
            xl8 offset-xl2
        >
            <v-card>
                <v-card-text>
                    <v-layout row wrap class="pa-0 ma-0">
                        <!-- Results Language Selector Start -->
                        <v-flex xs12 lg3>
                            <v-select
                                v-model="selectedLocale"
                                :items="availableLocales"
                                :label="$t('search.show_in.label')"
                                :placeholder="$t('search.show_in.placeholder')"
                                dense
                                outlined
                            />
                        </v-flex>
                        <!-- Results Language Selector End -->

                        <!-- Search Field Input Start -->
                        <v-flex xs12 lg6>
                            <v-text-field
                                v-model="query"
                                :label="$t('search.query_title')"
                                :placeholder="$t('search.query_placeholder')"
                                clearable
                                dense
                                outlined
                                @keyup="startSearching"
                            />
                        </v-flex>
                        <!-- Search Field Input End -->
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>
        <!-- Top Bar End -->

        <!-- Results Container Start -->
        <v-flex
            xs12
            lg10 offset-lg1
            xl8 offset-xl2
            class="pt-5"
        >
            <v-layout row wrap>
                <v-flex
                    xs12
                    md6
                    xl4
                    v-for="(item, index) in results"
                    :key="`media-item-flex-element-for-result-with-index-${index}`"
                    class="pa-2"
                >
                    <v-hover v-slot:default="{ hover }">
                        <v-card class="border-none">
                            <v-card-text class="pa-0">
                                <v-layout row wrap>
                                    <!-- Media Image Container Start -->
                                    <v-flex
                                        xs12
                                        sm2
                                        md4
                                        class="pt-0 pb-0"
                                    >
                                        <v-img
                                            contain
                                            :src="isMobilePortrait ? item.backdrop.w300 : item.poster.w92"
                                            :srcset="generateSrcSet(isMobilePortrait ? item.backdrop : item.poster)"
                                            max-height="260"
                                        />
                                    </v-flex>
                                    <!-- Media Image Container End -->
                                    <v-flex
                                        xs12
                                        sm10
                                        md8
                                        class="pl-2"
                                    >
                                        <v-layout row wrap >
                                            <v-flex xs12 class="pt-3">
                                                <v-layout row wrap>
                                                    <!-- Rating Element Start -->
                                                    <v-flex
                                                        xs2 offset-xs2
                                                        sm1 offset-sm0
                                                        md2
                                                        lg2
                                                    >
                                                        <v-progress-circular
                                                            :rotate="-90"
                                                            :size="40"
                                                            :width="3"
                                                            :value="item.vote_average * 10"
                                                            :color="getRatingColor(item.vote_average * 10)"
                                                        >
                                                            {{ item.vote_average * 10  }}
                                                        </v-progress-circular>
                                                    </v-flex>
                                                    <!-- Rating Element End -->

                                                    <!-- Name and Release Date Start -->
                                                    <v-flex xs8 sm10>
                                                        <v-layout row wrap>
                                                            <v-flex xs12 class="bold white--text pb-0">
                                                                {{ getItemTitle(item) }}
                                                            </v-flex>
                                                            <v-flex xs12 class="pt-0">
                                                                {{ $moment(item.release_date).format('MMMM D, YYYY') }}
                                                            </v-flex>
                                                        </v-layout>
                                                    </v-flex>
                                                    <!-- Name and Release Date End -->
                                                </v-layout>
                                            </v-flex>
                                            <v-flex xs12 class="pa-2">
                                                {{ trimItemOverview(item.overview[selectedLocale].value) }}
                                            </v-flex>
                                        </v-layout>
                                    </v-flex>
                                </v-layout>
                                <v-overlay
                                    :value="hover"
                                    :absolute="true"
                                    color="rgba(0, 0, 0, 1)"
                                >
                                    <v-btn
                                        color="primary"
                                        @click="showMoreInformation(item)"
                                    >
                                        {{ $t('search.show_more') }}
                                    </v-btn>
                                </v-overlay>
                            </v-card-text>
                        </v-card>
                    </v-hover>
                </v-flex>
            </v-layout>
        </v-flex>
        <!-- Results Container End -->

        <!-- Show More Dialog Start -->
        <v-dialog
            v-model="showMoreDialog"
            max-width="700"
        >
            <v-card v-if="showMoreDialog">
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-layout row wrap>
                                <!-- Media Image Overlay + Details Start -->
                                <v-flex xs12>
                                    <v-img
                                        class="white--text align-end"
                                        :src="showMoreItem.backdrop.w300"
                                        :srcset="generateSrcSet(showMoreItem.backdrop)"
                                        :height="200"
                                        gradient="to top right, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)"

                                    >
                                        <v-container>
                                            <v-layout row wrap class="pa-3">
                                                <v-flex xs12 class="fs-24 shadow-10">
                                                    {{ showMoreItem.title[selectedLocale].value }}
                                                </v-flex>
                                                <v-flex xs12 class="shadow-6">
                                                    {{ $t('dashboard.indexers.preview.released', { date: showMoreItem.release_date }) }}
                                                </v-flex>
                                                <v-flex xs12 class="pt-2">
                                                    <v-chip
                                                        v-for="(genre, index) in showMoreItem.genres.map((genre) => genre.name)"
                                                        :key="`v-chip-for-genre-with-index-${index}`"
                                                        small
                                                        class="mr-2"
                                                    >
                                                        {{ genre }}
                                                    </v-chip>
                                                </v-flex>
                                            </v-layout>
                                        </v-container>
                                    </v-img>
                                </v-flex>
                                <!-- Media Image Overlay + Details End -->

                                <!-- Media Overview Start -->
                                <v-flex xs12 lg10 offset-lg1 text-center class="pa-2 pt-4">
                                    {{ showMoreItem.overview[selectedLocale].value }}
                                </v-flex>
                                <!-- Media Overview End -->

                                <!-- Select Server Message Start -->
                                <v-flex xs12 v-show="showMoreItem.plex.length > 0" text-center class="pt-3 bold fs-18 white--text">
                                    {{ $t('common.select_server') }}
                                </v-flex>
                                <!-- Select Server Message End -->

                                <!-- Server Selector Start -->
                                <v-flex
                                    xs12
                                    v-for="(server, index) in showMoreItem.plex"
                                    :key="`server-flex-element-for-server-with-index-${index}`"
                                    class="pa-2"
                                    text-center
                                >
                                    <v-btn
                                        color="amber darken-2"
                                        target="_blank"
                                        :href="server.plex_url"
                                        min-width="250"
                                    >
                                        {{ server.server_name }}
                                    </v-btn>
                                </v-flex>
                                <!-- Server Selector End -->

                                <!-- Order Button Start -->
                                <v-flex xs12 class="pa-2" text-center v-show="showMoreItem.plex.length === 0 && authenticated">
                                    <v-btn
                                        color="primary"
                                        @click.stop="orderItem(showMoreItem)"
                                        min-width="250"
                                        :disabled="showMoreItem.requested"
                                    >
                                        <span v-if="!showMoreItem.requested">
                                            {{ $t('search.order') }}
                                        </span>
                                        <span v-else>
                                            <span v-if="showMoreItem.request_status !== 2">
                                                {{ $t('search.requested') }}
                                            </span>
                                            <span v-else>
                                                {{ $t('search.request_denied') }}
                                            </span>
                                        </span>
                                    </v-btn>
                                </v-flex>
                                <!-- Order Button End -->
                            </v-layout>
                        </v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-dialog>
        <!-- Show More Dialog End -->
    </v-layout>
</template>
<script>
    import debounce from 'lodash/debounce';
    import { mapGetters } from 'vuex';

    export default {
        data: () => ({
            selectedLocale: 'ru',
            query: '',
            results: [],
            availableLocales: [
                {
                    text: 'العربية',
                    value: 'ar'
                },
                {
                    text: 'Deutsch',
                    value: 'de'
                },
                {
                    text: 'English',
                    value: 'en'
                },
                {
                    text: 'Español',
                    value: 'es'
                },
                {
                    text: 'Français',
                    value: 'fr'
                },
                {
                    text: '日本語',
                    value: 'ja'
                },
                {
                    text: '한국어',
                    value: 'ko'
                },
                {
                    text: 'Norsk',
                    value: 'no'
                },
                {
                    text: 'Русский',
                    value: 'ru'
                },
                {
                    text: 'Українська',
                    value: 'uk'
                },
                {
                    text: '中文',
                    value: 'zh'
                }
            ],
            showMoreDialog: false,
            showMoreItem: [],
        }),
        watch: {
            query(current, previous) {
                if (current === null || current === undefined || current.length === 0) {
                    this.results = [];
                    this.showMoreItem = [];
                }
            },
            showServerSelectionDialog(current, previous) {
                if (current === false) {
                    this.selectedSeriesName = null;
                    this.serversList = [];
                }
            },
            showMoreDialog(current, previous) {
                if (current === false) {
                    this.showMoreItem = [];
                }
            }
        },
        computed: {
            ...mapGetters({
                authenticated: 'account/authenticated',
                user: 'account/user'
            }),
            isMobilePortrait() {
                return this.$vuetify.breakpoint.name === 'xs';
            }
        },
        mounted() {
            this.selectedLocale = this.$i18n.locale;
        },
        methods: {
            startSearching: debounce(function () {
                if (this.query.length <= 2) {
                    this.results = [];
                } else {
                    this.$axios.post('search/local', {
                        query: this.query
                    }).then(({ data }) => {
                        this.results = data.data;
                    });
                }
            }, 500),
            getItemTitle(item) {
                return item.title[this.selectedLocale].value;
            },
            getItemOriginalTitle(item) {
                return item.original_title;
            },
            getItemGenres(item) {
                return item.genre.map((item, index) => item.name).join(', ');
            },
            trimItemOverview(item, count = 35) {
                if (item === null || item === undefined) {
                    return '';
                }
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
            },
            showMoreInformation(item) {
                this.showMoreItem = item;
                setTimeout(() => {
                    this.showMoreDialog = true;
                }, 200);
            },
            orderItem(item) {
                this.$axios.post('requests/create', {
                    title: item.title.en.value,
                    released: item.release_date,
                    type: item.type
                }).then(() => {
                    this.showMoreDialog = false;
                    setTimeout(() => {
                        this.showMoreItem = [];
                    }, 200);
                    this.query = '';
                }).catch(({ response }) => {
                    console.log(response.data);
                });
            }
        }
    };
</script>

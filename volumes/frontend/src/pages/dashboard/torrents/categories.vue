<template>
    <v-responsive class="text-center pb-4">
        <v-container grid-list-xl>
            <v-layout row wrap>
                <v-flex xs12 text-center class="headline">
                    {{ $t('dashboard.torrents.torrent_categories') }}
                </v-flex>
            </v-layout>
            <v-layout justify-center wrap>
                <v-flex
                    v-for="(category, index) in categories"
                    :key="`flex-element-for-category-with-index-${index}`"
                    d-flex
                    shrink
                >
                    <v-card>
                        <v-card-text>
                            <v-layout row wrap>
                                <!-- Category Icon Start -->
                                <v-flex xs12>
                                    <v-icon size="65">
                                        {{ category.icon }}
                                    </v-icon>
                                </v-flex>
                                <!-- Category Icon End -->

                                <!-- Category Name Start -->
                                <v-flex xs12 class="headline">
                                    {{ category.name }}
                                </v-flex>
                                <!-- Category Name End -->

                                <!-- Category Create Button Start -->
                                <v-flex xs12>
                                    <v-btn
                                        color="green"
                                        block
                                        :loading="category.creating"
                                        @click.stop="createCategory(category)"
                                    >
                                        {{ $t('dashboard.torrents.create_category') }}
                                    </v-btn>
                                </v-flex>
                                <!-- Category Create Button End -->
                            </v-layout>
                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-container>
    </v-responsive>
</template>
<script>
    export default {
        layout: 'dashboard',
        data() {
            return {
                categories: [
                    {
                        name: this.$t('dashboard.torrents.categories.series'),
                        icon: 'local_movies',
                        value: 'series',
                        creating: false
                    },
                    {
                        name: this.$t('dashboard.torrents.categories.movies'),
                        icon: 'movie',
                        value: 'movies',
                        creating: false
                    },
                    // {
                    //     name: this.$t('dashboard.torrents.categories.music'),
                    //     icon: 'library_music',
                    //     value: 'Music',
                    //     creating: false
                    // }
                ]
            };
        },
        methods: {
            createCategory(category) {
                category.creating = true;
                this.$axios.post('/dashboard/torrents/create-category', {
                    category: category.value
                }).then(() => {
                    category.creating = false;
                });
            }
        }
    };
</script>

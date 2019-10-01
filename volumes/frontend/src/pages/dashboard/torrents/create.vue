<template>
    <v-layout row wrap>
        <!-- Add Torrent Page: Card Start -->
        <v-flex xs12 lg6 offset-lg3>
            <v-card>
                <v-card-title>
                    {{ $t('dashboard.torrents.create.title') }}
                    <v-spacer/>
                    <v-btn
                        @click.stop="createNewTorrent"
                        color="primary"
                        :loading="uploading"
                        :disabled="!canUpload"
                    >
                        Create Torrent
                    </v-btn>
                </v-card-title>
                <v-card-text>
                    <v-layout row wrap>
                        <!-- File Input Start -->
                        <v-flex xs12>
                            <v-file-input
                                v-model="files"
                                counter
                                multiple
                                accept="application/x-bittorrent"
                                :label="$t('dashboard.torrents.create.select_file')"
                                :placeholder="$t('dashboard.torrents.create.select_file_placeholder')"
                            />
                        </v-flex>
                        <!-- File Input End -->

                        <!-- Category Selector Start -->
                        <v-flex xs12>
                            <v-select
                                v-model="category"
                                :items="categories"
                                :label="$t('dashboard.torrents.create.select_category')"
                                :placeholder="$t('dashboard.torrents.create.select_category_placeholder')"
                                item-text="name"
                                item-value="value"
                                prepend-icon="category"
                            />
                        </v-flex>
                        <!-- Category Selector End -->
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>
        <!-- Add Torrent Page: Card End -->
    </v-layout>
</template>
<script>
    import forEach from 'lodash/forEach';

    export default {
        layout: 'dashboard',
        data() {
            return {
                files: [],
                category: null,
                uploading: false,

                categories: [
                    {
                        name: this.$t('dashboard.torrents.categories.series'),
                        value: 'series'
                    },
                    {
                        name: this.$t('dashboard.torrents.categories.movies'),
                        value: 'movies'
                    },
                    // {
                    //     name: this.$t('dashboard.torrents.categories.music'),
                    //     value: 'music'
                    // }
                ]
            };
        },
        computed: {
            canUpload() {
                return this.files.length > 0 && (this.category !== null && this.category !== undefined);
            }
        },
        beforeDestroy() {
            this.resetForm();
        },
        methods: {
            resetForm() {
                this.files = [];
                this.category = null;
            },
            createNewTorrent() {
                this.uploading = true;
                let formData = new FormData;
                forEach(this.files, (file, index) => {
                    formData.append('files[]', file, file.name);
                });
                formData.append('category', this.category);
                this.$axios.post('dashboard/torrents/create-torrent', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(() => {
                    this.uploading = false;
                    this.resetForm();
                });
            }
        },
    };
</script>

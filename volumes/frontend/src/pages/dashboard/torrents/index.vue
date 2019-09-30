<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-card>
                <v-card-text>
                    <v-data-table
                        :headers="headers"
                        :items="torrents"
                        :items-per-page="10"
                        :loading="loading"
                    >
                        <template v-slot:top>
                            <v-toolbar flat>
                                <v-toolbar-title>
                                    {{ $t('dashboard.torrents.title') }}
                                </v-toolbar-title>
                                <v-divider
                                    class="mx-4"
                                    inset
                                    vertical
                                />
                                <div class="flex-grow-1"/>
                                <v-btn
                                    @click.stop="automaticUpdate = !automaticUpdate"
                                    :color="automaticUpdate ? 'green' : 'red'"
                                >
                                    <v-progress-circular
                                        v-show="automaticUpdate"
                                        indeterminate
                                        color="white"
                                        :width="2"
                                        :size="25"
                                    />
                                    <span class="ml-2">
                                        {{ $t('dashboard.torrents.automatic_update') }}
                                    </span>
                                </v-btn>
                                <v-btn
                                    @click.stop="fetchTorrentsList"
                                    color="primary"
                                    class="ml-2"
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
                                :key="`torrents-table-row-for-item-with-index-${index}`"
                            >
                                <td>
                                    {{ item.hash }}
                                </td>
                                <td align="center">
                                    {{ item.name }}
                                </td>
                                <td align="center">
                                    {{ formatBytes(item.downloaded, true) }} / {{ formatBytes(item.size, true) }} ({{ calculatePercentage(item) }} %)
                                </td>
                                <td align="center">
                                    {{ formatBytes(item.dlspeed) }} | {{ formatBytes(item.upspeed) }}
                                </td>
                                <td align="center">
                                    {{ item.category }}
                                </td>
                                <td align="center">
                                    {{ item.num_seeds }}
                                </td>
                                <td align="center">
                                    {{ $t('dashboard.torrents.statuses.' + item.state) }}
                                </td>
                                <td align="center">
                                    <!-- Torrent Controls: Start Button Start -->
                                    <v-icon
                                        small
                                        @click.stop="startTorrent(item)"
                                        v-show="item.state !== 'downloading'"
                                    >
                                        play_arrow
                                    </v-icon>
                                    <!-- Torrent Controls: Start Button End -->

                                    <!-- Torrent Controls: Pause Button Start -->
                                    <v-icon
                                        class="ml-2"
                                        small
                                        @click.stop="stopTorrent(item)"
                                        v-show="item.state !== 'pausedDL'"
                                    >
                                        pause
                                    </v-icon>
                                    <!-- Torrent Controls: Pause Button End -->

                                    <!-- Torrent Controls: Delete Button Start -->
                                    <v-icon
                                        class="ml-2"
                                        small
                                        @click.stop="showDeleteTorrent(item)"
                                    >
                                        delete
                                    </v-icon>
                                    <!-- Torrent Controls: Delete Button End -->
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-data-table>
                </v-card-text>
            </v-card>
        </v-flex>
        <v-dialog
            v-model="deleteDialog"
            persistent
            max-width="400"
        >
            <v-card v-if="deleteDialog">
                <v-card-title>
                    {{ $t('dashboard.torrents.delete') }}
                </v-card-title>
                <v-card-text>
                    <v-checkbox
                        dark
                        color="primary"
                        v-model="deleteTorrent.force"
                        :label="$t('dashboard.torrents.delete_files')"
                    />
                </v-card-text>
                <v-card-actions>
                    <div class="flex-grow-1"/>
                    <v-btn
                        outlined
                        color="green"
                        @click="cancelTorrentDeletion"
                    >
                        {{ $t('common.cancel') }}
                    </v-btn>
                    <v-btn
                        outlined
                        color="red"
                        @click="confirmTorrentDeletion"
                        :loading="deleting"
                    >
                        {{ $t('common.delete') }}
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
                loading: false,
                automaticUpdate: false,
                automaticUpdateTimer: null,
                deleteDialog: false,
                deleting: false,
                deleteTorrent: {
                    hash: null,
                    force: false
                },
                headers: [
                    {
                        text: this.$t('dashboard.torrents.headers.hash'),
                        align: 'left',
                        sortable: true,
                        value: 'hash',
                    },
                    {
                        text: this.$t('dashboard.torrents.headers.name'),
                        align: 'center',
                        value: 'name',
                    },
                    {
                        text: this.$t('dashboard.torrents.headers.progress'),
                        align: 'center',
                        value: 'downloaded',
                    },
                    {
                        text: this.$t('dashboard.torrents.headers.speed'),
                        align: 'center',
                        value: 'dlspeed',
                    },
                    {
                        text: this.$t('dashboard.torrents.headers.category'),
                        align: 'center',
                        value: 'category',
                    },
                    {
                        text: this.$t('dashboard.torrents.headers.seeds'),
                        align: 'center',
                        value: 'num_seeds',
                    },
                    {
                        text: this.$t('dashboard.torrents.headers.state'),
                        align: 'center',
                        value: 'state',
                    },
                    {
                        text: this.$t('dashboard.torrents.headers.actions'),
                        align: 'center',
                        value: 'hash',
                    },
                ]
            };
        },
        computed: {
            ...mapGetters({
                torrents: 'dashboard/torrents'
            })
        },
        watch: {
            automaticUpdate(current, previous) {
                if (current) {
                    this.automaticUpdateTimer = setInterval(this.fetchTorrentsList, 5000);
                } else {
                    clearInterval(this.automaticUpdateTimer);
                }
            }
        },
        beforeDestroy() {
            this.automaticUpdate = false;
            clearInterval(this.automaticUpdateTimer);
        },
        mounted() {
            if (
                this.torrents === null
                || this.torrents === undefined
                || this.torrents.length === 0
            ) {
                this.fetchTorrentsList();
            }
            this.resetDefaultTorrentObject();
        },
        methods: {
            fetchTorrentsList() {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchTorrentsList').then(() => {
                    this.loading = false;
                });
            },
            resetDefaultTorrentObject() {
                this.deleteTorrent = {
                    hash: null,
                    force: false
                };
            },
            calculatePercentage(torrent) {
                return Math.round((torrent.downloaded / torrent.size) * 100 * 100) / 100;
            },
            startTorrent(torrent) {
                this.$axios.post('/dashboard/torrents/resume', {
                    hash: torrent.hash
                }).then(() => {
                    setTimeout(() => {
                        this.fetchTorrentsList();
                    }, 1200);
                });
            },
            stopTorrent(torrent) {
                this.$axios.post('/dashboard/torrents/pause', {
                    hash: torrent.hash
                }).then(() => {
                    setTimeout(() => {
                        this.fetchTorrentsList();
                    }, 1200);
                });
            },
            showDeleteTorrent(torrent) {
                this.deleteTorrent = Object.assign({}, {
                    hash: torrent.hash,
                    force: false
                });
                setTimeout(() => {
                    this.deleteDialog = true;
                }, 200);
            },
            confirmTorrentDeletion() {
                this.deleting = true;
                this.$axios.post('/dashboard/torrents/delete', this.deleteTorrent).then(() => {
                    setTimeout(() => {
                        this.deleting = false;
                        this.deleteDialog = false;
                        setTimeout(() => {
                            this.resetDefaultTorrentObject();
                        }, 200);
                    }, 2000);
                });
            },
            cancelTorrentDeletion() {
                this.deleteDialog = false;
                setTimeout(() => {
                    this.resetDefaultTorrentObject();
                }, 200);
            }
        }
    };
</script>

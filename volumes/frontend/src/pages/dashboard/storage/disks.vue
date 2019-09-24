<template>
    <v-layout row wrap>
        <!-- Pool Information Block Start -->
        <v-flex xs12 text-center class="pb-5">
            <v-layout row wrap>
                <v-flex xs12 lg3 offset-lg4>
                    <v-card :style="computePoolInformationColor" :elevation="0">
                        <v-card-text class="white--text">
                            <v-layout row wrap text-center>
                                <v-flex xs12>
                                    <strong class="fs-20">
                                        {{ $t('dashboard.storage.disks.pool_information') }}
                                    </strong>
                                </v-flex>
                                <v-flex xs12 class="pt-4">
                                    <v-layout row wrap>
                                        <!-- Pool Free Space Block Start -->
                                        <v-flex xs12 lg6>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="fs-28">
                                                    {{ poolSizeFreeReadable }}
                                                </v-flex>
                                                <v-flex xs12>
                                                    {{ $t('common.free') }}
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <!-- Pool Free Space Block End -->

                                        <!-- Pool Total Space Block Start -->
                                        <v-flex xs12 lg6>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="fs-28">
                                                    {{ poolSizeTotalReadable }}
                                                </v-flex>
                                                <v-flex xs12>
                                                    {{ $t('common.total') }}
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <!-- Pool Total Space Block End -->
                                    </v-layout>
                                </v-flex>
                                <v-flex xs12 class="pt-3">
                                    <v-btn
                                        color="primary"
                                        text
                                        :loading="loading"
                                        @click.stop="refreshPoolData"
                                    >
                                        {{ $t('dashboard.storage.disks.refresh_pool_data') }}
                                    </v-btn>
                                </v-flex>
                            </v-layout>
                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-flex>
        <!-- Pool Information Block End -->
        <v-flex xs12>
            <v-card>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex
                            xs12
                            v-for="(driveData, driveName) in disks"
                            :key='`layout-flex-item-for-drive-with-name-${driveName}`'
                        >
                            <v-card :style="cardInCardStyle">
                                <v-card-text>
                                    <v-layout row wrap>
                                        <!-- Drive First Information Block Start -->
                                        <v-flex xs12 lg2>
                                            <v-layout row wrap>
                                                <!-- Drive Icon Block Start -->
                                                <v-flex xs12 lg6 text-center>
                                                    <v-icon size="48">
                                                        storage
                                                    </v-icon>
                                                </v-flex>
                                                <!-- Drive Icon Block End -->

                                                <!-- Drive Name Block Start -->
                                                <v-flex xs12 lg6>
                                                    <v-layout row wrap>
                                                        <v-flex xs12 class="card-block-title">
                                                            {{ $t('dashboard.storage.disks.disk_name') }}
                                                        </v-flex>
                                                        <v-flex xs12>
                                                            {{ driveName }}
                                                        </v-flex>
                                                    </v-layout>
                                                </v-flex>
                                                <!-- Drive Name Block End -->

                                                <!-- Drive Local Mount Block Start -->
                                                <v-flex xs12>
                                                    <v-layout row wrap>
                                                        <v-flex xs12 class="card-block-title">
                                                            {{ $t('dashboard.storage.disks.local_mount') }}
                                                        </v-flex>
                                                        <v-flex xs12>
                                                            <v-layout row wrap>
                                                                <v-flex
                                                                    xs12
                                                                    v-for="(mount, index) in driveData.media.mounts"
                                                                    :key="`layout-flex-item-for-drive-with-name-${driveName}-and-mount-with-index-${index}`"
                                                                >
                                                                    {{ mount }}
                                                                </v-flex>
                                                            </v-layout>
                                                        </v-flex>
                                                    </v-layout>
                                                </v-flex>
                                                <!-- Drive Local Mount Block End -->

                                                <!-- Drive Remote Mount Block Start -->
                                                <v-flex xs12>
                                                    <v-layout row wrap>
                                                        <v-flex xs12 class="card-block-title">
                                                            {{ $t('dashboard.storage.disks.remote_mount') }}
                                                        </v-flex>
                                                        <v-flex xs12>
                                                            {{ driveData.remote_mount }}
                                                        </v-flex>
                                                    </v-layout>
                                                </v-flex>
                                                <!-- Drive Remote Mount Block End -->
                                            </v-layout>
                                        </v-flex>
                                        <!-- Drive First Information Block End -->

                                        <!-- Drive Second Information Block Start -->
                                        <v-flex xs12 lg6>
                                            <v-layout row wrap>
                                                <!-- Drive Usage Progress Start -->
                                                <v-flex xs12>
                                                    <v-progress-linear
                                                        :value="driveData.percentage.used"
                                                        :color="computeDriveProgressColor(driveData.percentage.used)"
                                                        height="25"
                                                        reactive
                                                    >
                                                        <template v-slot="{ value }">
                                                            <strong>
                                                                {{ driveData.used_space.nice }} / {{ driveData.total_space.nice }} ({{ Math.round(((driveData.used_space.exact / driveData.total_space.exact) * 100) * 100) / 100 }} %)
                                                            </strong>
                                                        </template>
                                                    </v-progress-linear>
                                                </v-flex>
                                                <!-- Drive Usage Progress End -->

                                                <!-- Drive Media Information Start -->
                                                <v-flex xs12 lg10 offset-lg2 class="pt-5">
                                                    <v-layout row wrap>
                                                        <!-- Series Count Block Start -->
                                                        <v-flex xs12 lg4>
                                                            <v-layout row wrap>
                                                                <v-flex xs12 class="card-block-title">
                                                                    {{ $t('dashboard.storage.disks.media.series_count_title') }}:
                                                                </v-flex>
                                                                <v-flex xs12>
                                                                    {{ driveData.media.series.series }} / {{ driveData.media.series.episodes }}
                                                                </v-flex>
                                                            </v-layout>
                                                        </v-flex>
                                                        <!-- Series Count Block End -->

                                                        <!-- Series Size Block Start -->
                                                        <v-flex xs12 lg4>
                                                            <v-layout row wrap>
                                                                <v-flex xs12 class="card-block-title">
                                                                    {{ $t('dashboard.storage.disks.media.size_title') }}:
                                                                </v-flex>
                                                                <v-flex xs12>
                                                                    {{ driveData.media.series.size.nice }}
                                                                </v-flex>
                                                            </v-layout>
                                                        </v-flex>
                                                        <!-- Series Size Block End -->

                                                        <!-- Series Percentage Block Start -->
                                                        <v-flex xs12 lg4>
                                                            <v-layout row wrap>
                                                                <v-flex xs12 class="card-block-title">
                                                                    {{ $t('dashboard.storage.disks.media.percentage_title') }}:
                                                                </v-flex>
                                                                <v-flex xs12>
                                                                    {{ calculateMediaSizePercentage(driveData, 'series') }} %
                                                                </v-flex>
                                                            </v-layout>
                                                        </v-flex>
                                                        <!-- Series Percentage Block End -->

                                                        <!-- Movies Count Block Start -->
                                                        <v-flex xs12 lg4>
                                                            <v-layout row wrap>
                                                                <v-flex xs12 class="card-block-title">
                                                                    {{ $t('dashboard.storage.disks.media.movies_count_title') }}:
                                                                </v-flex>
                                                                <v-flex xs12>
                                                                    {{ driveData.media.movies.count }}
                                                                </v-flex>
                                                            </v-layout>
                                                        </v-flex>
                                                        <!-- Movies Count Block End -->

                                                        <!-- Movies Size Block Start -->
                                                        <v-flex xs12 lg4>
                                                            <v-layout row wrap>
                                                                <v-flex xs12 class="card-block-title">
                                                                    {{ $t('dashboard.storage.disks.media.size_title') }}:
                                                                </v-flex>
                                                                <v-flex xs12>
                                                                    {{ driveData.media.movies.size.nice }}
                                                                </v-flex>
                                                            </v-layout>
                                                        </v-flex>
                                                        <!-- Movies Size Block End -->

                                                        <!-- Movies Percentage Block Start -->
                                                        <v-flex xs12 lg4>
                                                            <v-layout row wrap>
                                                                <v-flex xs12 class="card-block-title">
                                                                    {{ $t('dashboard.storage.disks.media.percentage_title') }}:
                                                                </v-flex>
                                                                <v-flex xs12>
                                                                    {{ calculateMediaSizePercentage(driveData, 'movies') }} %
                                                                </v-flex>
                                                            </v-layout>
                                                        </v-flex>
                                                        <!-- Movies Percentage Block End -->
                                                    </v-layout>
                                                </v-flex>
                                                <!-- Drive Media Information End -->
                                            </v-layout>
                                        </v-flex>
                                        <!-- Drive Second Information Block End -->

                                        <!-- Drive Third Information Block Start -->
                                        <v-flex xs12 lg4>
                                            <v-layout row wrap>
                                                <v-flex xs12>
                                                    DRIVE ACTIONS GO HERE
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <!-- Drive Third Information Block End -->
                                    </v-layout>
                                </v-card-text>
                            </v-card>
                        </v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>
    </v-layout>
</template>
<script>
    import { mapGetters } from 'vuex';
    import forEach from 'lodash/forEach';

    export default {
        layout: 'dashboard',
        data: () => ({
            loading: false,
        }),
        computed: {
            ...mapGetters({
                disks: 'dashboard/storage_disks'
            }),
            poolSizeTotal() {
                let poolSize = 0;
                forEach(this.disks, (drive) => {
                    poolSize += drive.total_space.exact;
                });
                return poolSize;
            },
            poolSizeTotalReadable() {
                return this.formatBytes(this.poolSizeTotal);
            },
            poolSizeFree() {
                let poolSize = 0;
                forEach(this.disks, (drive) => {
                    poolSize += drive.free_space.exact;
                });
                return poolSize;
            },
            poolSizeFreeReadable() {
                return this.formatBytes(this.poolSizeFree);
            },
            computePoolInformationColor() {
                let color = '#008700';
                const freeSize = this.poolSizeFree;
                const totalSize = this.poolSizeTotal;
                const poolPercentageValue = Math.trunc((freeSize / totalSize) * 100);

                if (poolPercentageValue < 70 && poolPercentageValue > 30) {
                    color = '#e2b016';
                } else if (poolPercentageValue < 30) {
                    color = '#cc0025';
                }
                return `background-color: #171e26!important; border-style: solid; border-color: ${color};`;
            }
        },
        mounted() {
            if (
                this.disks === null
                || this.disks === undefined
                || this.disks.length === 0
            ) {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchStorageDisksList').then(() => {
                    this.loading = false;
                });
            }
        },
        methods: {
            computeDriveProgressColor(usedPercentage) {
                let color = 'green';
                if (usedPercentage > 30 && usedPercentage < 70) {
                    color = 'amber';
                } else if (usedPercentage > 70) {
                    color = 'red';
                }
                return color;
            },
            calculateMediaSizePercentage(driveData, mediaType) {
                return Math.round(((driveData.media[mediaType].size.exact / driveData.used_space.exact) * 100) * 100) / 100;
            },
            refreshPoolData() {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchStorageDisksList').then(() => {
                    this.loading = false;
                });
            }
        }
    };
</script>

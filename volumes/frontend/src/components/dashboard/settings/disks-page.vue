<template>
    <v-card flat class="border-none">
        <v-card-text>
            <v-layout row wrap>
                <!-- Disks Preferred Drive Selector Start -->
                <v-flex xs12 lg4 offset-lg2>
                    <v-select
                        v-model="settings.parameters.preferred_drive.value.value"
                        :items="createPreferredDrivesList()"
                        :label="$t('dashboard.settings.disks.preferred.label')"
                        :placeholder="$t('dashboard.settings.disks.preferred.placeholder')"
                        :hint="$t('dashboard.settings.disks.preferred.hint')"
                        :sensitive="settings.parameters.preferred_drive.value.sensitive"
                        persistent-hint
                        outlined
                    />
                </v-flex>
                <!-- Disks Preferred Drive Selector End -->

                <!-- Disks Threshold Type Selector Start -->
                <v-flex xs12 lg4>
                    <v-select
                        v-model="settings.parameters.threshold_percentage.value.value"
                        :items="thresholdTypes"
                        :label="$t('dashboard.settings.disks.type.label')"
                        :placeholder="$t('dashboard.settings.disks.type.placeholder')"
                        outlined
                    />
                </v-flex>
                <!-- Disks Threshold Type Selector End -->

                <!-- Disk Threshold Value Selector Start -->
                <v-flex xs12 lg4 offset-lg2 v-if="!settings.parameters.threshold_percentage.value.value">
                    <v-text-field
                        v-model="settings.parameters.threshold.value.value"
                        :label="$t('dashboard.settings.disks.threshold.label')"
                        :placeholder="$t('dashboard.settings.disks.threshold.placeholder')"
                        type="number"
                        outlined
                    />
                </v-flex>
                <!-- Disk Threshold Value Selector End -->

                <!-- Disk Threshold Units Selector Start -->
                <v-flex xs12 lg4 v-if="!settings.parameters.threshold_percentage.value.value">
                    <v-select
                        v-model="settings.parameters.threshold_units.value.value"
                        :items="thresholdUnits"
                        :label="$t('dashboard.settings.disks.units.label')"
                        :placeholder="$t('dashboard.settings.disks.units.placeholder')"
                        outlined
                    ></v-select>
                </v-flex>
                <!-- Disk Threshold Units Selector End -->

                <!-- Disk Threshold Percentage Slider Start -->
                <v-flex xs12 lg4 offset-lg4 v-if="settings.parameters.threshold_percentage.value.value">
                    <v-slider
                        v-model="settings.parameters.threshold.value.value"
                        thumb-label="always"
                        :label="$t('dashboard.settings.disks.threshold.label')"
                    />
                </v-flex>
                <!-- Disk Threshold Percentage Slider End -->
            </v-layout>
        </v-card-text>
    </v-card>
</template>
<script>
    import forEach from 'lodash/forEach';

    export default {
        name: 'dashboard-settings-disks',
        props: {
            settings: {
                type: Object,
                required: true
            }
        },
        data() {
            return {
                thresholdTypes: [
                    {
                        text: this.$t('dashboard.settings.disks.type.percentage'),
                        value: true
                    },
                    {
                        text: this.$t('dashboard.settings.disks.type.units'),
                        value: false
                    }
                ],
                thresholdUnits: [
                    {
                        text: this.$t('dashboard.settings.disks.units.kb'),
                        value: 'KB'
                    },
                    {
                        text: this.$t('dashboard.settings.disks.units.mb'),
                        value: 'MB'
                    },
                    {
                        text: this.$t('dashboard.settings.disks.units.gb'),
                        value: 'GB'
                    },
                    {
                        text: this.$t('dashboard.settings.disks.units.tb'),
                        value: 'TB'
                    },
                    {
                        text: this.$t('dashboard.settings.disks.units.pb'),
                        value: 'PB'
                    }
                ]
            };
        },
        methods: {
            createPreferredDrivesList() {
                const list = [];
                forEach(this.settings.drives, (info, drive) => {
                    list.push({
                        text: `${this.$t('dashboard.settings.disks.drive', {
                            drive: drive,
                            used: info.used_space.nice,
                            total: info.total_space.nice,
                            percentage: info.percentage.used
                        })}`,
                        value: drive
                    });
                });
                return list;
            }
        }
    };
</script>

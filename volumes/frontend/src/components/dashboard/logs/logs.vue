<template>
    <v-layout row wrap>
        <v-flex
            v-if="loading"
            xs12
            lg2
            offset-lg5
            class="pt-5"
        >
            <v-card>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs12 text-center>
                            <v-progress-circular
                                indeterminate
                                color="primary"
                                :size="60"
                            />
                        </v-flex>
                        <v-flex xs12 text-center class="fs-18 pt-3">
                            {{ $t('dashboard.logs.loading_logs') }}
                        </v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>
        <v-flex
            v-if="!loading && !hasData"
            xs12
            lg2
            offset-lg5
            class="pt-5"
        >
            <v-card>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs12 text-center class="fs-18">
                            {{ $t('dashboard.logs.no_logs') }}
                        </v-flex>
                        <v-flex xs12 text-center class="pt-3">
                            <v-btn
                                color="primary"
                                @click.stop="fetchLogs"
                                block
                            >
                                {{ $t('common.refresh' )}}
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>
        <v-flex
            xs12
            v-for="(logsList, date) in logs"
            :key="`logs-flex-element-for-date-${date}`"
            v-else
        >
            <v-card>
                <v-card-text>
                    <v-data-table
                        :headers="headers"
                        :items="logsList"
                        :items-per-page="10"
                    >
                        <template v-slot:top>
                            <v-toolbar flat>
                                <v-toolbar-title>
                                    {{ $t('dashboard.logs.for_date', { date: date }) }}
                                </v-toolbar-title>
                                <v-divider
                                    class="mx-4"
                                    inset
                                    vertical
                                />
                                <div class="flex-grow-1"/>
                                <v-btn
                                    @click.stop="fetchLogs"
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
                            <tr v-for="item in items" :key="item.id">
                                <td>
                                    {{ item.id }}
                                </td>
                                <td>{{ item.context.message }}</td>
                                <td align="center">{{ item.environment }}</td>
                                <td align="center">
                                    <v-chip
                                        :color="createStyleForLogLevel(item.level)"
                                    >
                                        {{ item.level }}
                                    </v-chip>
                                </td>
                                <td align="center">{{ toLocalTime(item.date) }}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-data-table>
                </v-card-text>
            </v-card>
        </v-flex>
    </v-layout>
</template>
<script>
    import { mapGetters } from 'vuex';
    import forEach from 'lodash/forEach';
    import omit from 'lodash/omit';

    export default {
        name: 'dashboard-logs-page',
        props: {
            type: {
                type: String,
                required: true
            }
        },
        data() {
            return {
                loading: false,
                logs: {},
                headers: [
                    {
                        text: this.$t('dashboard.logs.headers.id'),
                        align: 'left',
                        sortable: false,
                        value: 'id'
                    },
                    {
                        text: this.$t('dashboard.logs.headers.message'),
                        value: 'context.message',
                        align: 'center',
                        sortable: false
                    },
                    {
                        text: this.$t('dashboard.logs.headers.environment'),
                        value: 'environment',
                        align: 'center'
                    },
                    {
                        text: this.$t('dashboard.logs.headers.level'),
                        value: 'level',
                        align: 'center'
                    },
                    {
                        text: this.$t('dashboard.logs.headers.time'),
                        value: 'date',
                        align: 'center'
                    },
                ],
                hasData: false
            };
        },
        computed: {
            ...mapGetters({
                unfilteredLogs: 'dashboard/logs'
            })
        },
        mounted() {
            (this.unfilteredLogs === null || this.unfilteredLogs === undefined || this.unfilteredLogs.length === 0) ? this.fetchLogs() : this.applyFilters();
        },
        methods: {
            fetchLogs() {
                this.loading = true;
                this.$store.dispatch('dashboard/fetchLogs').then(() => {
                    this.applyFilters();
                    this.loading = false;
                });
            },
            applyFilters() {
                if (this.type !== 'all') {
                    forEach(this.unfilteredLogs, (logs, date) => {
                        if (!this.logs.hasOwnProperty(date)) {
                            this.logs[date] = [];
                        }
                        forEach(logs, (log, index) => {
                            if (log.level === this.type) {
                                this.logs[date].push(log);
                            }
                        });
                    });
                    forEach(this.logs, (logs, date) => {
                        if (logs.length === 0) {
                            this.logs = omit(this.logs, date);
                        }
                    });
                } else {
                    this.logs = Object.assign({}, this.unfilteredLogs);
                }
                setTimeout(() => {
                    this.checkIfDataPresentAfterLoad();
                }, 500);
            },
            createStyleForLogLevel(logLevel) {
                let color = '';
                switch (logLevel) {
                    case 'error':
                        color = 'red';
                        break;
                    case 'warning':
                        color = 'amber darken-3';
                        break;
                    case 'info':
                        color = 'blue';
                        break;
                    default:
                        break;
                }
                return color;
            },
            checkIfDataPresentAfterLoad() {
                let hasData = true;
                if (!this.loading) {
                    if (this.isObjectActuallyArray(this.logs)) {
                        hasData = this.logs.length !== 0;
                    } else {
                        if (Object.keys(this.logs).length > 0) {
                            hasData = true;
                        } else {
                            hasData = false;
                        }
                    }
                } else {
                    hasData = false;
                }

                this.hasData = hasData;
            }
        }
    };
</script>

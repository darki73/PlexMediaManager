<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-card>
                <v-card-title class="fs-16">
                    <h1>Requests >> All</h1>
                </v-card-title>
                <v-card-text>
                    <v-data-table
                        :headers="headers"
                        :items="requests"
                        :items-per-page="10"
                    >
                        <template v-slot:body="{ items }">
                            <tbody>
                                <tr
                                    v-for="(item, index) in items"
                                    :key="`requests-table-row-for-item-with-index-${index}`"
                                >
                                    <td>
                                        {{ item.id }}
                                    </td>
                                    <td align="center">
                                        {{ $t('dashboard.requests.types.' + item.request_type) }}
                                    </td>
                                    <td align="center">
                                        {{ item.title }}
                                    </td>
                                    <td align="center">
                                        {{ item.year }}
                                    </td>
                                    <td align="center">
                                        {{ item.user.username }}
                                    </td>
                                    <td align="center">
                                        {{ $t('dashboard.requests.statuses.' + item.status) }}
                                    </td>
                                    <td align="center">
                                        {{ item.created_at }}
                                    </td>
                                    <td align="center">
                                        CRUD
                                    </td>
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

    export default {
        layout: 'dashboard',
        data() {
            return {
                headers: [
                    {
                        text: this.$t('dashboard.requests.headers.id'),
                        align: 'left',
                        sortable: false,
                        value: 'id',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.type'),
                        align: 'center',
                        value: 'request_type',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.title'),
                        align: 'center',
                        value: 'title',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.year'),
                        align: 'center',
                        value: 'year',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.username'),
                        align: 'center',
                        value: 'user.username',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.status'),
                        align: 'center',
                        value: 'status',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.created'),
                        align: 'center',
                        value: 'created_at',
                    },
                    {
                        text: this.$t('dashboard.requests.headers.actions'),
                        align: 'center',
                        value: 'id',
                    },
                ]
            };
        },
        computed: {
            ...mapGetters({
                requests: 'dashboard/requests'
            })
        },
        mounted() {
            if (
                this.requests === null
                || this.requests === undefined
                || this.requests.length === 0
            ) {
                this.$store.dispatch('dashboard/fetchRequests');
            }
        }
    };
</script>

<template>
    <v-layout row wrap>
        <!-- Server Information Cards Start -->
        <v-flex xs12>
            <v-layout row wrap>
                <!-- Server Information Card Start -->
                <v-flex class="equal-cards-height" xs12 lg6 xl4>
                    <v-card>
                        <v-card-title>
                            {{ $t('dashboard.server.card_title') }}
                        </v-card-title>
                        <v-card-text>
                            <v-layout row wrap>
                                <v-flex xs12 lg4>
                                    <v-layout row wrap>
                                        <!-- Distributive Image Start -->
                                        <v-flex xs12>
                                            <v-img
                                                :src="`/images/os/${kernel.os.toLowerCase()}.png`"
                                                aspect-ratio="1"
                                            />
                                        </v-flex>
                                        <!-- Distributive Image End -->

                                        <!-- Updates Button Start -->
                                        <v-flex xs12>
                                            <v-btn
                                                block
                                                color="primary"
                                                v-show="serverInformation.updates"
                                            >
                                                {{ $t('dashboard.server.updates.available') }}
                                            </v-btn>
                                            <v-btn
                                                block
                                                v-show="!serverInformation.updates"
                                                disabled
                                            >
                                                {{ $t('dashboard.server.updates.none') }}
                                            </v-btn>
                                        </v-flex>
                                        <!-- Updates Button End -->
                                    </v-layout>
                                </v-flex>
                                <v-flex xs12 lg7 offset-lg1>
                                    <v-layout row wrap>
                                        <!-- Kernel Information: OS Version Start -->
                                        <v-flex xs12>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                    {{ $t('dashboard.server.os.version') }}:
                                                </v-flex>
                                                <v-flex xs12>
                                                    {{ kernel.os }} {{ kernel.os_version }}<br />
                                                    <small class="os-build-info">
                                                        ({{ $t('dashboard.server.os.build', { date: kernel.build_date }) }})
                                                    </small>
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <!-- Kernel Information: OS Version End -->

                                        <!-- Kernel Information: Processor Start -->
                                        <v-flex xs12>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                    {{ $t('dashboard.server.processor.processor') }}:
                                                </v-flex>
                                                <v-flex xs12>
                                                    {{ $t('dashboard.server.processor.model', { vendor: processor.vendor, model: processor.model, frequency: readableFrequency(processor.frequency), cores: processor.cores, threads: processor.threads })}}
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <!-- Kernel Information: Processor End -->

                                        <!-- Kernel Information: Memory Start -->
                                        <v-flex xs12>
                                            <v-layout row wrap>
                                                <v-flex xs12 lg6>
                                                    <v-layout row wrap>
                                                        <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                            {{ $t('dashboard.server.memory.total') }}:
                                                        </v-flex>
                                                        <v-flex xs12>
                                                            {{ memory.total.nice }}
                                                        </v-flex>
                                                    </v-layout>
                                                </v-flex>
                                                <v-flex xs12 lg6>
                                                    <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                        {{ $t('dashboard.server.memory.available') }}:
                                                    </v-flex>
                                                    <v-flex xs12>
                                                        {{ memory.available.nice }}
                                                    </v-flex>
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <!-- Kernel Information: Memory End -->

                                        <!-- Kernel Information: Uptime Start -->
                                        <v-flex xs12>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                    {{ $t('dashboard.server.uptime') }}:
                                                </v-flex>
                                                <v-flex xs12>
                                                    {{ serverInformation.uptime }}
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <!-- Kernel Information: Uptime End -->
                                    </v-layout>
                                </v-flex>
                            </v-layout>
                        </v-card-text>
                    </v-card>
                </v-flex>
                <!-- Server Information Card End -->

                <!-- Network Information Card Start -->
                <v-flex class="equal-cards-height" xs12 lg6 xl4>
                    <v-card>
                        <v-card-title>
                            {{ $t('dashboard.network.card_title') }}
                        </v-card-title>
                        <v-card-text>
                            <v-layout row wrap>
                                <!-- Backend Network Information Start -->
                                <v-flex xs12>
                                    <v-layout row wrap>
                                        <v-flex xs12 lg4>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                    {{ $t('dashboard.network.backend.domain') }}:
                                                </v-flex>
                                                <v-flex xs12>
                                                    <a :href="`//${network.backend.domain}`" class="no-underline" target="_blank">
                                                        {{ network.backend.domain }}
                                                    </a>
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <v-flex xs12 lg4>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                    {{ $t('dashboard.network.backend.local_ip') }}:
                                                </v-flex>
                                                <v-flex xs12>
                                                    {{ network.backend.local_ip }}
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <v-flex xs12 lg4>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                    {{ $t('dashboard.network.backend.remote_ip') }}:
                                                </v-flex>
                                                <v-flex xs12>
                                                    {{ manipulateRemoteIP(network.backend.remote_ip) }}
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                    </v-layout>
                                </v-flex>
                                <!-- Backend Network Information End -->

                                <!-- Frontend Network Information Start -->
                                <v-flex xs12>
                                    <v-layout row wrap>
                                        <v-flex xs12 lg4>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                    {{ $t('dashboard.network.frontend.domain') }}:
                                                </v-flex>
                                                <v-flex xs12>
                                                    <a :href="`//${network.frontend.domain}`" class="no-underline" target="_blank">
                                                        {{ network.frontend.domain }}
                                                    </a>
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <v-flex xs12 lg4>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                    {{ $t('dashboard.network.frontend.local_ip') }}:
                                                </v-flex>
                                                <v-flex xs12>
                                                    {{ network.frontend.local_ip }}
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                        <v-flex xs12 lg4>
                                            <v-layout row wrap>
                                                <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                    {{ $t('dashboard.network.frontend.remote_ip') }}:
                                                </v-flex>
                                                <v-flex xs12>
                                                    {{ manipulateRemoteIP(network.frontend.remote_ip) }}
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                    </v-layout>
                                </v-flex>
                                <!-- Frontend Network Information End -->

                                <!-- DNS Network Information Start -->
                                <v-flex xs12>
                                    <v-layout row wrap>
                                        <v-flex
                                            xs12
                                            lg4
                                            v-for="(server, index) in network.nameservers"
                                            :key="`flex-item-for-nameserver-with-index-${index}`"
                                        >
                                            <v-layout row wrap>
                                                <v-flex xs12 class="card-block-title pb-0 mb-0">
                                                    {{ $t('dashboard.network.dns') }} {{ index + 1 }}:
                                                </v-flex>
                                                <v-flex xs12>
                                                    {{ server.domain }}<br />
                                                    <small class="os-build-info">
                                                        ({{ server.ip }})
                                                    </small>
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                    </v-layout>
                                </v-flex>
                                <!-- DNS Network Information End -->
                            </v-layout>
                        </v-card-text>
                    </v-card>
                </v-flex>
                <!-- Network Information Card End -->
            </v-layout>
        </v-flex>
        <!-- Server Information Cards End -->
    </v-layout>
</template>
<script>
    import { mapGetters } from 'vuex';

    export default {
        layout: 'dashboard',
        async fetch({ store, params }) {
            const serverInformationGetter = store.getters['dashboard/server'];
            if (serverInformationGetter === null || serverInformationGetter === undefined || serverInformationGetter.length === 0) {
                await store.dispatch('dashboard/fetchServerInformation');
            }
        },
        data: () => ({}),
        computed: {
            ...mapGetters({
                serverInformation: 'dashboard/server'
            }),
            kernel() {
                return this.serverInformation.kernel;
            },
            processor() {
                return this.serverInformation.processor;
            },
            memory() {
                return this.serverInformation.memory;
            },
            network() {
                return this.serverInformation.network;
            }
        },
        mounted() {
            if (this.serverInformation === null || this.serverInformation === undefined) {
                this.$store.dispatch('dashboard/fetchServerInformation');
            }
        },
        methods: {
            manipulateRemoteIP(ipAddress) {
                if (this.configuration.site.environment === 'local') {
                    return 'dont.display.while.local';
                }
                return ipAddress;
            }
        }
    };
</script>
<style scoped lang="scss">
    .os-build-info {
        font-size: 12px;
        color: gray;
        font-style: italic;
    }
</style>

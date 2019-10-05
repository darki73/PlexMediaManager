<template>
    <v-layout row wrap>
        <v-flex xs12 lg8 offset-lg2>
            <v-card>
                <v-card-text>
                    <v-layout row wrap>
                        <!-- Settings Loader Start -->
                        <v-flex xs12 v-if="loading">
                            <v-layout row wrap>
                                <v-flex xs12 text-center>
                                    <v-progress-circular
                                        indeterminate
                                        color="primary"
                                        :size="60"
                                    />
                                </v-flex>
                                <v-flex xs12 text-center class="fs-18 pt-3">
                                    {{ $t('dashboard.settings.loading_settings') }}
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <!-- Settings Loader End -->

                        <!-- Settings Tab Selector Start -->
                        <v-flex xs12 v-if="!loading">
                            <v-tabs
                                v-model="selectedTab"
                                centered
                                icons-and-text
                            >
                                <v-tabs-slider/>
                                <v-tab
                                    v-for="(tab, index) in tabs"
                                    :key="`tab-selector-for-tab-with-index-${index}`"
                                    :href="`#${tab.href}`"
                                    :class="selectedTab !== tab.href ? 'white--text' : ''"
                                >
                                    {{ tab.text }}
                                    <v-icon>
                                        {{ tab.icon }}
                                    </v-icon>
                                </v-tab>
                            </v-tabs>
                        </v-flex>
                        <!-- Settings Tab Selector End -->

                        <!-- Settings Tabs Start -->
                        <v-flex xs12 v-if="!loading">
                            <v-tabs-items v-model="selectedTab">
                                <v-tab-item
                                    v-for="(tab, index) in tabs"
                                    :key="`tab-item-for-tab-with-index-${index}`"
                                    :value="tab.href"
                                >
                                    <component :is="tab.component" :settings="settings[tab.href]"/>
                                </v-tab-item>
                            </v-tabs-items>
                        </v-flex>
                        <!-- Settings Tabs End -->
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>
    </v-layout>
</template>
<script>
    import DashboardSettingsEnvironmentPage from '~/components/dashboard/settings/environment-page';
    import DashboardSettingsDisksPage from '~/components/dashboard/settings/disks-page';
    import DashboardSettingsProxyPage from '~/components/dashboard/settings/proxy-page';

    export default {
        layout: 'dashboard',
        components: {
            'dashboard-settings-environment': DashboardSettingsEnvironmentPage,
            'dashboard-settings-disks': DashboardSettingsDisksPage,
            'dashboard-settings-proxy': DashboardSettingsProxyPage,
        },
        data() {
            return {
                loading: true,
                selectedTab: null,
                tabs: [
                    {
                        text: this.$t('dashboard.settings.tabs.environment'),
                        icon: 'account_tree',
                        href: 'environment',
                        component: 'dashboard-settings-environment'
                    },
                    {
                        text: this.$t('dashboard.settings.tabs.disks'),
                        icon: 'storage',
                        href: 'disks',
                        component: 'dashboard-settings-disks'
                    },
                    {
                        text: this.$t('dashboard.settings.tabs.proxy'),
                        icon: 'network_locked',
                        href: 'proxy',
                        component: 'dashboard-settings-proxy'
                    },
                ],
                settings: []
            };
        },
        beforeDestroy() {
            this.resetPage();
        },
        mounted() {
            this.resetPage();
            this.fetchSettings();
        },
        methods: {
            resetPage() {
                this.selectedTab = null;
                this.settings = [];
            },
            fetchSettings() {
                this.loading = true;
                this.$axios.get('dashboard/settings').then(({ data }) => {
                    this.settings = Object.assign({}, data.data);
                    setTimeout(() => {
                        this.loading = false;
                    }, 1000);
                });
            }
        }
    };
</script>

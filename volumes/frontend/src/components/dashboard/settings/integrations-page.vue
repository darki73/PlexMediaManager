<template>
    <v-card flat class="border-none">
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12>
                    <v-tabs
                        v-model="selectedTab"
                        centered
                        dark
                        icons-and-text
                    >
                        <v-tabs-slider/>
                        <v-tab
                            v-for="(data, index) in settings"
                            :key="`tab-for-integration-with-index-of-${index}`"
                            :href="`#${data.integration}`"
                        >
                            {{ data.integration }}
                            <v-icon>
                                mdi-{{ data.integration }}
                            </v-icon>
                        </v-tab>
                    </v-tabs>
                </v-flex>
                <v-flex xs12>
                    <v-tabs-items v-model="selectedTab">
                        <v-tab-item
                            v-for="(data, index) in settings"
                            :key="`tab-content-for-integration-with-index-of-${index}`"
                            :value="data.integration"
                        >
                            <v-card flat>
                                <v-card-text>
                                    <v-layout row wrap>
                                        <v-flex xs12 lg3>
                                            <v-layout row wrap>
                                                <!-- Integration Enabled Switch Start -->
                                                <v-flex xs12>
                                                    <v-switch
                                                        v-model="data.enabled"
                                                        :label="data.enabled ? $t('dashboard.settings.integrations.enabled') : $t('dashboard.settings.integrations.disabled')"
                                                        color="primary"
                                                        inset
                                                    />
                                                </v-flex>
                                                <!-- Integration Enabled Switch End -->

                                                <!-- Integration Saver Button Start -->
                                                <v-flex xs12>
                                                    <v-btn
                                                        block
                                                        color="primary"
                                                        @click.stop="updateIntegrationSettings(data)"
                                                        :disabled="!checkIfIntegrationFilledIn(data)"
                                                    >
                                                        {{ $t('dashboard.settings.integrations.update') }}
                                                    </v-btn>
                                                </v-flex>
                                                <!-- Integration Saver Button End -->

                                                <!-- Integration OAuth Button Start -->
                                                <v-flex xs12 v-show="showOAuthButton(data)">
                                                    <v-btn
                                                        block
                                                        color="primary"
                                                        @click.stop="startOAuthAuthorizationProcedure"
                                                        :disabled="!checkIfIntegrationFilledIn(data)"
                                                        :loading="oauthAuthenticationProcessStarted"
                                                    >
                                                        {{ $t('dashboard.settings.integrations.oauth') }}
                                                    </v-btn>
                                                </v-flex>
                                                <!-- Integration OAuth Button End -->
                                            </v-layout>
                                        </v-flex>
                                        <v-flex xs12 lg8 offset-lg1>
                                            <v-layout row wrap>
                                                <v-flex
                                                    xs12
                                                    v-for="(value, key) in data.configuration"
                                                    :key="`tab-content-for-integration-with-index-of-${index}-and-for-key-${key}`"
                                                >
                                                    <v-sensitive-input
                                                        v-model="data.configuration[key]"
                                                        :label="key"
                                                        :placeholder="$t('dashboard.settings.integrations.placeholder', { item: key })"
                                                        sensitive
                                                        :disabled="!data.enabled"
                                                        outlined
                                                        :required="data.validation.hasOwnProperty('required') ? data.validation.required.includes(key) : false"
                                                    />
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                    </v-layout>
                                </v-card-text>
                            </v-card>
                        </v-tab-item>
                    </v-tabs-items>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
</template>
<script>
    import { mapGetters } from 'vuex';
    import forEach from 'lodash/forEach';

    export default {
        name: 'dashboard-settings-proxy',
        props: {
            settings: {
                type: Array,
                required: true
            }
        },
        data: () => ({
            selectedTab: null,
            oauthAuthenticationProcessStarted: false,
            oauthAuthenticationWindowInterval: null,
            oauthAuthenticationWindow: null,
            oauthAuthenticationData: {
                url: null,
                id: null,
                code: null
            }
        }),
        computed: {
            ...mapGetters({
                user: 'account/user'
            })
        },
        mounted() {
            forEach(this.settings, (integration) => {
                this.$echo.private(`account.${this.user.id}`).listen(`.${integration.integration}.continue`, ({ code }) => {
                    this.oauthAuthenticationData.code = code;
                    this.continueOAuthAuthenticationProcess();
                });
            });
        },
        methods: {
            checkIfIntegrationFilledIn(data) {
                let valid = true;
                const requiredParameters = data.validation.hasOwnProperty('required') ? data.validation.required : [];
                const configuration = data.configuration;
                forEach(configuration, (value, key) => {
                    if (requiredParameters.includes(key)) {
                        if (value === null || value === undefined || value.length === 0) {
                            valid = false;
                        }
                    }
                });
                return valid;
            },
            showOAuthButton(data) {
                const integrationEnabled = data.enabled;
                const oauthEnabled = data.oauth;
                const configuration = data.configuration;
                const requiredParameters = data.validation.hasOwnProperty('oauth') ? data.validation.oauth : [];
                const integrationRequiredFilledIn = this.checkIfIntegrationFilledIn(data);
                let oauthCompleted = true;

                forEach(configuration, (value, key) => {
                    if (requiredParameters.includes(key)) {
                        if (value === null || value === undefined || value.length === 0) {
                            oauthCompleted = false;
                        }
                    }
                });

                if (integrationRequiredFilledIn) {
                    return integrationEnabled && oauthEnabled && !oauthCompleted;
                }
                return false;
            },
            updateIntegrationSettings(data) {
                const integration = this.selectedTab;
                this.$axios.post(`dashboard/settings/update-integration/${integration}`, {
                    enabled: data.enabled,
                    configuration: data.configuration
                }).then(() => {
                    this.$bus.$emit('settingsUpdateRequested');
                });
            },
            startOAuthAuthorizationProcedure() {
                const self = this;
                self.oauthAuthenticationProcessStarted = true;
                const integration = self.selectedTab;
                self.$axios.get(`account/oauth/${integration}`).then(({ data }) => {
                    self.oauthAuthenticationData = data.data;
                    if (process.client) {
                        self.oauthAuthenticationWindow = window.open(
                            self.oauthAuthenticationData.url,
                            '_blank',
                            [
                                'location=no',
                                'height=700',
                                'width=500'
                            ].join(',')
                        );
                        self.oauthAuthenticationWindowInterval = setInterval(() => {
                            if (self.oauthAuthenticationWindow.closed) {
                                self.resetOAuthAuthenticationWindowInterval();
                                self.oauthAuthenticationProcessStarted = false;
                            }
                        }, 1000);
                    }
                });
            },
            resetOAuthAuthenticationData() {
                this.oauthAuthenticationData = {
                    url: null,
                    id: null,
                    code: null
                };
            },
            resetOAuthAuthenticationWindowInterval() {
                clearInterval(this.oauthAuthenticationWindowInterval);
                this.oauthAuthenticationWindowInterval = null;
                this.oauthAuthenticationWindow = null;
            },
            continueOAuthAuthenticationProcess() {
                const self = this;
                const integration = self.selectedTab;
                self.resetOAuthAuthenticationWindowInterval();
                self.$axios.post(`account/oauth/${integration}`, {
                    code: self.oauthAuthenticationData.code
                }).then(() => {
                    self.resetOAuthAuthenticationData();
                    self.resetOAuthAuthenticationWindowInterval();
                    self.oauthAuthenticationProcessStarted = false;
                    self.$bus.$emit('settingsUpdateRequested');
                }).catch(({ response }) => {
                    self.resetOAuthAuthenticationData();
                    self.resetOAuthAuthenticationWindowInterval();
                    self.oauthAuthenticationProcessStarted = false;
                    console.log(response.data);
                });
            }
        }
    };
</script>

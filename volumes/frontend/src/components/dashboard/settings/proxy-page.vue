<template>
    <v-card flat class="border-none">
        <v-card-text>
            <v-layout row wrap>
                <!-- Proxy Type Selector Start -->
                <v-flex xs12 lg4>
                    <v-select
                        v-model="settings.parameters.type.value.value"
                        :items="createProxyTypes(settings.allowed_types)"
                        :label="$t('dashboard.settings.proxy.type.label')"
                        :placeholder="$t('dashboard.settings.proxy.type.placeholder')"
                        :sensitive="settings.parameters.type.value.sensitive"
                        outlined
                    />
                </v-flex>
                <!-- Proxy Type Selector End -->

                <!-- Proxy Host Input Start -->
                <v-flex xs12 lg4>
                    <v-sensitive-input
                        v-model="settings.parameters.host.value.value"
                        :label="$t('dashboard.settings.proxy.host.label')"
                        :placeholder="$t('dashboard.settings.proxy.host.placeholder')"
                        :sensitive="settings.parameters.host.value.sensitive"
                        outlined
                    />
                </v-flex>
                <!-- Proxy Host Input End -->

                <!-- Proxy Port Input Start -->
                <v-flex xs12 lg4>
                    <v-sensitive-input
                        v-model="settings.parameters.port.value.value"
                        type="number"
                        :label="$t('dashboard.settings.proxy.port.label')"
                        :placeholder="$t('dashboard.settings.proxy.port.placeholder')"
                        :sensitive="settings.parameters.port.value.sensitive"
                        outlined
                    />
                </v-flex>
                <!-- Proxy Port Input End -->

                <!-- Proxy Username Input Start -->
                <v-flex xs12 lg4 offset-lg2>
                    <v-sensitive-input
                        v-model="settings.parameters.username.value.value"
                        :label="$t('dashboard.settings.proxy.username.label')"
                        :placeholder="$t('dashboard.settings.proxy.username.placeholder')"
                        :hint="$t('dashboard.settings.proxy.username.hint')"
                        :sensitive="settings.parameters.username.value.sensitive"
                        persistent-hint
                        outlined
                    />
                </v-flex>
                <!-- Proxy Username Input End -->

                <!-- Proxy Password Input Start -->
                <v-flex xs12 lg4>
                    <v-sensitive-input
                        v-model="settings.parameters.password.value.value"
                        :label="$t('dashboard.settings.proxy.password.label')"
                        :placeholder="$t('dashboard.settings.proxy.password.placeholder')"
                        :hint="$t('dashboard.settings.proxy.password.hint')"
                        :sensitive="settings.parameters.password.value.sensitive"
                        persistent-hint
                        outlined
                    />
                </v-flex>
                <!-- Proxy Password Input End -->
            </v-layout>
        </v-card-text>
    </v-card>
</template>
<script>
    import forEach from 'lodash/forEach';

    export default {
        name: 'dashboard-settings-proxy',
        props: {
            settings: {
                type: Object,
                required: true
            }
        },
        methods: {
            createProxyTypes(allowedTypes) {
                const types = [];

                forEach(allowedTypes, (text, value) => {
                    types.push({
                        text: text,
                        value: value
                    });
                });

                return types;
            }
        }
    };
</script>

<template>
    <v-card flat class="border-none">
        <v-card-text>
            <v-layout row wrap>
                <!-- Application Name Input Start -->
                <v-flex xs12 lg4>
                    <v-sensitive-input
                        v-model="settings.parameters.name.value.value"
                        :label="$t('dashboard.settings.environment.name.label')"
                        :placeholder="$t('dashboard.settings.environment.name.placeholder')"
                        outlined
                        :sensitive="settings.parameters.name.value.sensitive"
                    />
                </v-flex>
                <!-- Application Name Input End -->

                <!-- Application URL Input Start -->
                <v-flex xs12 lg4>
                    <v-sensitive-input
                        v-model="settings.parameters.url.value.value"
                        :label="$t('dashboard.settings.environment.url.label')"
                        :placeholder="$t('dashboard.settings.environment.url.placeholder')"
                        :sensitive="settings.parameters.url.value.sensitive"
                        outlined
                    />
                </v-flex>
                <!-- Application URL Input End -->

                <!-- Application Key Input Start -->
                <v-flex xs12 lg4>
                    <v-sensitive-input
                        v-model="settings.parameters.key.value.value"
                        :label="$t('dashboard.settings.environment.key.label')"
                        :placeholder="$t('dashboard.settings.environment.key.placeholder')"
                        :hint="$t('dashboard.settings.environment.key.hint')"
                        :sensitive="settings.parameters.key.value.sensitive"
                        outlined
                        persistent-hint
                    />
                </v-flex>
                <!-- Application Key Input End -->

                <!-- Application Environment Selector Start -->
                <v-flex xs12 lg4 offset-lg2>
                    <v-select
                        v-model="settings.parameters.env.value.value"
                        :items="environments"
                        :label="$t('dashboard.settings.environment.env.label')"
                        :placeholder="$t('dashboard.settings.environment.env.placeholder')"
                        outlined
                    />
                </v-flex>
                <!-- Application Environment Selector End -->

                <!-- Application Debug Mode Selector Start -->
                <v-flex xs12 lg4>
                    <v-select
                        v-model="settings.parameters.debug.value.value"
                        :items="debug"
                        :label="$t('dashboard.settings.environment.debug.label')"
                        :placeholder="$t('dashboard.settings.environment.debug.placeholder')"
                        outlined
                    />
                </v-flex>
                <!-- Application Debug Mode Selector End -->

            </v-layout>
        </v-card-text>
    </v-card>
</template>
<script>
    export default {
        name: 'dashboard-settings-environment',
        props: {
            settings: {
                type: Object,
                required: true
            }
        },
        data() {
            return {
                environments: [
                    {
                        text: this.$t('dashboard.settings.environment.env.local'),
                        value: 'local'
                    },
                    {
                        text: this.$t('dashboard.settings.environment.env.production'),
                        value: 'production'
                    }
                ],
                debug: [
                    {
                        text: this.$t('dashboard.settings.environment.debug.enabled'),
                        value: true
                    },
                    {
                        text: this.$t('dashboard.settings.environment.debug.disabled'),
                        value: false
                    }
                ]
            };
        }
    };
</script>

<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-card>
                <v-card-text>
                    <v-data-table
                        :headers="headers"
                        :items="items"
                        :items-per-page="10"
                        :loading="loading"
                    >
                        <template v-slot:top>
                            <v-toolbar flat>
                                <v-toolbar-title>
                                    {{ $t('dashboard.indexers.title') }}
                                </v-toolbar-title>
                                <v-divider
                                    class="mx-4"
                                    inset
                                    vertical
                                />
                                <div class="flex-grow-1"/>
                                <v-btn
                                    @click.stop="refreshIndexers"
                                    color="primary"
                                >
                                    <v-icon>
                                        refresh
                                    </v-icon>
                                </v-btn>
                            </v-toolbar>
                        </template>
                    </v-data-table>
                </v-card-text>
            </v-card>
        </v-flex>
    </v-layout>
</template>
<script>
    export default {
        layout: 'dashboard',
        data: () => ({
            loading: true,
            headers: [

            ],


            items: []
        }),
        methods: {
            refreshIndexers() {

            }
        }
    };
</script>

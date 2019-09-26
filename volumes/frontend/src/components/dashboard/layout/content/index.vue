<template>
    <v-content>
        <v-container fluid grid-list-md>
            <v-card>
                <v-card-text>
                    <project-info/>
                </v-card-text>
            </v-card>
            <nuxt/>
        </v-container>
    </v-content>
</template>
<script>
    export default {
        name: 'dashboard-layout-content'
    };
</script>

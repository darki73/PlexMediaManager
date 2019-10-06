<template>
    <v-content>
        <v-container fluid grid-list-md :fill-height="fillHeight">
            <nuxt/>
        </v-container>
    </v-content>
</template>
<script>
    export default {
        name: 'layout-content',
        props: {
            fillHeight: {
                type: Boolean,
                required: false,
                default: () => false
            }
        }
    };
</script>

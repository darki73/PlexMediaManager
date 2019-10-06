<template>
    <v-layout row wrap>
        <v-flex xs12>
            <pre>{{ user }}</pre>
        </v-flex>
    </v-layout>
</template>
<script>
    import { mapGetters } from 'vuex';

    export default {
        layout: 'default',
        data: () => ({}),
        computed: {
            ...mapGetters({
                authenticated: 'account/authenticated',
                user: 'account/user'
            })
        }
    };
</script>

<template>
    <v-layout row wrap>
        <v-flex xs12>
            <h1>Dashboard >> Accounts >> Groups</h1>
        </v-flex>
        <v-flex xs12>
            <pre>{{ groups }}</pre>
        </v-flex>
    </v-layout>
</template>
<script>
    import { mapGetters } from 'vuex';

    export default {
        layout: 'dashboard',
        data() {
            return {
                loading: false,
                headers: [

                ]
            };
        },
        computed: {
            ...mapGetters({
                groups: 'dashboard/accounts_groups'
            })
        },
        mounted() {
            // if (
            //     this.groups === null
            //     || this.groups === undefined
            //     || this.groups.length === 0
            // ) {
            //     this.loading = true;
            //     this.$store.dispatch('dashboard/fetchAccountsGroups').then(() => {
            //         this.loading = false;
            //     });
            // }
        }
    };
</script>

<template>
    <global-search/>
</template>
<script>
    import GlobalSearch from '~/components/global/search';

    export default {
        layout: 'dashboard',
        data: () => ({}),
        components: {
            'global-search': GlobalSearch
        }
    };
</script>

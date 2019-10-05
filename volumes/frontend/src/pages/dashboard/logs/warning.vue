<template>
    <dashboard-logs-page type="warning"/>
</template>
<script>
    import LogsPage from '~/components/dashboard/logs/logs';

    export default {
        layout: 'dashboard',
        components: {
            'dashboard-logs-page': LogsPage
        }
    };
</script>

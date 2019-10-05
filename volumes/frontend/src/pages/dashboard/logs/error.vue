<template>
    <dashboard-logs-page type="error"/>
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

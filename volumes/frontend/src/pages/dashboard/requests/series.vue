<template>
    <requests-page :type="0"/>
</template>
<script>
    import Requests from '~/components/dashboard/requests/requests';

    export default {
        layout: 'dashboard',
        components: {
            'requests-page': Requests
        }
    };
</script>

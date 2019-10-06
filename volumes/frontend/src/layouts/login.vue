<template>
    <v-app>
        <layout-content fill-height/>
    </v-app>
</template>
<script>
    import Content from '~/components/user/layout/content';

    export default {
        name: 'login-layout',
        components: {
            'layout-content': Content,
        }
    };
</script>

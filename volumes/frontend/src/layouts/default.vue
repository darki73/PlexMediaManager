<template>
    <v-app>
        <layout-header/>
        <layout-sidebar/>
        <layout-content/>
    </v-app>
</template>
<script>
    import Header from '~/components/user/layout/header';
    import Sidebar from '~/components/user/layout/sidebar';
    import Content from '~/components/user/layout/content';

    export default {
        name: 'default-layout',
        components: {
            'layout-header': Header,
            'layout-sidebar': Sidebar,
            'layout-content': Content,
        }
    };
</script>

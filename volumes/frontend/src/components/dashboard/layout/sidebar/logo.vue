<template>
    <v-toolbar
        color="primary"
        flat
    >
        <span v-show="!minimized">
            Have you seen logo? O_o
        </span>
        <span v-show="minimized">
            O_o
        </span>
    </v-toolbar>
</template>
<script>
    export default {
        name: 'dashboard-layout-sidebar-logo',
        data: () => ({
            minimized: false
        }),
        mounted() {
            this.$bus.$on('minimizeDrawer', (minimized) => {
                this.minimized = minimized;
            });
        }
    };
</script>

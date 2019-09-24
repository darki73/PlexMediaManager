<template>
    <v-app-bar
        app
        dark
        color="primary"
        fixed
    >
        <v-app-bar-nav-icon @click.stop="$bus.$emit('toggleDrawer')"/>
        <v-btn icon @click.stop="minimizeDrawer">
            <v-icon v-show="!minimized">
                keyboard_arrow_left
            </v-icon>
            <v-icon v-show="minimized">
                keyboard_arrow_right
            </v-icon>
        </v-btn>
    </v-app-bar>
</template>
<script>
    export default {
        name: 'dashboard-layout-header',
        data: () => ({
            minimized: false
        }),
        methods: {
            minimizeDrawer() {
                this.$bus.$emit('minimizeDrawer');
                this.minimized = !this.minimized;
            }
        }
    };
</script>

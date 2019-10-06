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
        <v-spacer/>
        <v-btn
            class="normal-case"
            color="red lighten-3"
            outlined
            nuxt
            to="/dashboard"
            v-if="authenticated && isAdministrator(user)"
        >
            {{ $t('user.menu.admin') }}
        </v-btn>
    </v-app-bar>
</template>
<script>
    import { mapGetters } from 'vuex';

    export default {
        name: 'layout-header',
        data: () => ({
            minimized: false
        }),
        computed: {
            ...mapGetters({
                authenticated: 'account/authenticated',
                user: 'account/user'
            })
        },
        methods: {
            minimizeDrawer() {
                this.minimized = !this.minimized;
                this.$bus.$emit('minimizeDrawer', this.minimized);
            }
        }
    };
</script>

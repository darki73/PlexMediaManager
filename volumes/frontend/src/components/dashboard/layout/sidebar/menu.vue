<template>
    <v-list
        dense
        nav
    >
        <template v-for="(item, index) in items">
            <dashboard-layout-sidebar-partials-menu-single :item="item" :key="`menu-single-entry-item-with-index-${index}`" v-if="!item.hasOwnProperty('items')"/>
            <dashboard-layout-sidebar-partials-menu-group :item="item" :key="`menu-group-entry-item-with-index-${index}`" v-if="item.hasOwnProperty('items')"/>
        </template>
    </v-list>
</template>
<script>
    import SingleMenuEntry from '~/components/dashboard/layout/sidebar/partials/menu/single';
    import GroupMenuEntry from '~/components/dashboard/layout/sidebar/partials/menu/group';

    export default {
        name: 'dashboard-layout-sidebar-menu',
        components: {
            'dashboard-layout-sidebar-partials-menu-single': SingleMenuEntry,
            'dashboard-layout-sidebar-partials-menu-group': GroupMenuEntry
        },
        data() {
            return {
                items: [
                    {
                        action: 'dashboard',
                        title: 'dashboard.menu.dashboard',
                        link: '/dashboard'
                    },
                    {
                        action: 'people',
                        title: 'dashboard.menu.account.accounts',
                        items: [
                            {
                                title: 'dashboard.menu.account.groups',
                                link: '/dashboard/account/groups',
                                icon: 'group_work'
                            },
                            {
                                title: 'dashboard.menu.account.users',
                                link: '/dashboard/account/users',
                                icon: 'group'
                            }
                        ]
                    },
                    {
                        action: 'playlist_add',
                        title: 'dashboard.menu.requests.requests',
                        items: [
                            {
                                title: 'dashboard.menu.requests.all',
                                link: '/dashboard/requests',
                                icon: 'folder_open'
                            },
                            {
                                title: 'dashboard.menu.requests.series',
                                link: '/dashboard/requests/series',
                                icon: 'movie'
                            },
                            {
                                title: 'dashboard.menu.requests.movies',
                                link: '/dashboard/requests/movies',
                                icon: 'local_movies'
                            },
                        ]
                    },
                    {
                        action: 'storage',
                        title: 'dashboard.menu.storage.storage',
                        items: [
                            {
                                title: 'dashboard.menu.storage.disks',
                                link: '/dashboard/storage/disks',
                                icon: 'storage'
                            },
                            {
                                title: 'dashboard.menu.storage.mounts',
                                link: '/dashboard/storage/mounts',
                                icon: 'sd_storage'
                            }
                        ]
                    },
                    {
                        action: 'insert_drive_file',
                        title: 'dashboard.menu.logs.logs',
                        items: [
                            {
                                title: 'dashboard.menu.logs.all',
                                link: '/dashboard/logs',
                                icon: 'folder'
                            },
                            {
                                title: 'dashboard.menu.logs.info',
                                link: '/dashboard/logs/info',
                                icon: 'info',
                                color: 'blue'
                            },
                            {
                                title: 'dashboard.menu.logs.warnings',
                                link: '/dashboard/logs/warning',
                                icon: 'warning',
                                color: 'amber'
                            },
                            {
                                title: 'dashboard.menu.logs.errors',
                                link: '/dashboard/logs/error',
                                icon: 'error_outline',
                                color: 'red'
                            },
                        ]
                    }
                ]
            };
        },
        methods: {

        }
    };
</script>

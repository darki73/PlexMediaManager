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
        components: {
            'dashboard-layout-sidebar-partials-menu-single': SingleMenuEntry,
            'dashboard-layout-sidebar-partials-menu-group': GroupMenuEntry
        },
        data() {
            return {
                items: [
                    {
                        action: 'home',
                        title: 'user.menu.home',
                        link: '/'
                    },
                    {
                        action: 'search',
                        title: 'user.menu.search',
                        link: '/search'
                    },
                    {
                        action: 'search',
                        title: 'user.menu.search_local',
                        link: '/local-search'
                    },
                    {
                        action: 'move_to_inbox',
                        title: 'user.menu.requests.requests',
                        items: [
                            {
                                title: 'user.menu.requests.all',
                                link: '/requests',
                                icon: 'inbox'
                            },
                            {
                                title: 'user.menu.requests.movies',
                                link: '/requests/movies',
                                icon: 'movie'
                            },
                            {
                                title: 'user.menu.requests.series',
                                link: '/requests/series',
                                icon: 'local_movies'
                            }
                        ]
                    },
                    {
                        action: 'stars',
                        title: 'user.menu.top.top',
                        items: [
                            {
                                title: 'user.menu.top.movies',
                                link: '/top/movies',
                                icon: 'movie'
                            },
                            {
                                title: 'user.menu.top.series',
                                link: '/top/series',
                                icon: 'local_movies'
                            }
                        ]
                    },
                ]
            };
        }
    };
</script>

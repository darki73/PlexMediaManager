<template>
    <v-list-item
        link
        nuxt
        :to="item.link"
        exact
    >
        <v-list-item-icon>
            <v-icon :color="item.color || null">{{ item.action }}</v-icon>
        </v-list-item-icon>
        <v-list-item-title>
            {{ $t(item.title) }}
        </v-list-item-title>
    </v-list-item>
</template>
<script>
    export default {
        name: 'dashboard-layout-sidebar-partials-menu-single',
        props: {
            item: {
                type: Object,
                required: true
            }
        }
    };
</script>

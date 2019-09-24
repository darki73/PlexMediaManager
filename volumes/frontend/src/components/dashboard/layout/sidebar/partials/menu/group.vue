<template>
    <v-list-group
        :prepend-icon="item.action"
    >
        <template v-slot:activator>
            <v-list-item-title>
                {{ $t(item.title) }}
            </v-list-item-title>
        </template>
        <v-list-item
            v-for="(entry, index) in item.items"
            :key="`menu-sub-group-entry-with-index-${index}-for-group-${item.action}`"
            link
            nuxt
            :to="entry.link"
            exact
        >
            <v-list-item-title>
                {{ $t(entry.title) }}
            </v-list-item-title>
            <v-list-item-icon>
                <v-icon :color="entry.color || null">
                    {{ entry.icon }}
                </v-icon>
            </v-list-item-icon>
        </v-list-item>
    </v-list-group>
</template>
<script>
    export default {
        name: 'dashboard-layout-sidebar-partials-menu-group',
        props: {
            item: {
                type: Object,
                required: true
            }
        }
    };
</script>

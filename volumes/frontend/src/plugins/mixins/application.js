import Vue from 'vue';
import forEach from 'lodash/forEach';
import siteConfiguration from '~/config/site';

Vue.mixin({
    data: () => ({
        configuration: {
            site: siteConfiguration
        }
    }),
    computed: {
        // Computed methods
        cardInCardStyle() {
            return 'background-color: #303c46!important';
        }
    },
    watch: {
        // Watcher
    },
    methods: {
        // Globally available "application" methods
        dateTime() {
            return new Date();
        },
        readableFrequency(frequency) {
            const newFrequency = frequency / 1000;
            return Math.round(newFrequency * 100) / 100 + 'Ghz';
        },
        formatBytes(bytes, si) {
            let threshold = si ? 1000 : 1024;
            if (Math.abs(bytes) < threshold) {
                return bytes + ' B';
            }
            let units = si
                ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
                : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
            let u = -1;
            do {
                bytes /= threshold;
                ++u;
            } while (Math.abs(bytes) >= threshold && u < units.length - 1);
            return bytes.toFixed(1) + ' ' + units[u];
        },
        toLocalDateTime(dateTime) {
            return this.$moment.utc(dateTime, 'YYYY-MM-DD HH:mm:ss').local().format('YYYY-MM-DD HH:mm:ss');
        },
        toLocalTime(time) {
            return this.$moment.utc(time, 'YYYY-MM-DD HH:mm:ss').local().format('HH:mm:ss');
        },
        generateSrcSet(items) {
            const sizes = [];
            forEach(items, (url, size) => {
                if (size !== 'original') {
                    sizes.push(`${url} ${size.replace('w', '')}w`);
                }
            });
            return sizes.join(',');
        },
        isObjectActuallyArray(object) {
            return object.constructor.toString().indexOf('Array') !== -1;
        },
        isAdministrator(user) {
            return user.roles[0].name.indexOf('administrator') !== -1;
        }
    },
    created() {
        // Actions to be executed when component is created
    },
    mounted() {
        // Actions to be executed when components is mounted
    },
    destroyed() {
        // Actions to be executed when component is destroyed
    },

});

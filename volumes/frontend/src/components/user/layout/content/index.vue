<template>
    <v-content>
        <v-container fluid grid-list-md :fill-height="fillHeight" :class="noPadding ? 'pa-0' : ''">
            <v-alert
                v-model="alert"
                type="info"
                color="primary darken-1"
                dismissible
            >
                <v-layout row wrap>
                    <v-flex
                        xs12
                        v-if="alertStack.length > 0"
                    >
                        {{ notification }}
                    </v-flex>
                    <v-flex xs12>
                        <v-progress-linear
                            :value="counter"
                            color="green lighten-3"
                        />
                    </v-flex>
                </v-layout>
            </v-alert>
            <nuxt/>
        </v-container>
    </v-content>
</template>
<script>
    export default {
        name: 'layout-content',
        props: {
            fillHeight: {
                type: Boolean,
                required: false,
                default: () => false
            },
            noPadding: {
                type: Boolean,
                required: false,
                default: () => false
            }
        },
        data: () => ({
            alert: false,
            counter: 0,
            alertStack: [],
            interval: null,
            counterInterval: null
        }),
        watch: {
            alertStack(current, previous) {
                const self = this;
                if (current.length === 0) {
                    self.clearInterval();
                    self.alert = false;
                } else {
                    self.alert = true;
                    this
                        .clearInterval()
                        .clearCounter()
                        .setCounter()
                        .setInterval();
                }
            }
        },
        computed: {
            notification() {
                return this.alertStack[0] || [];
            }
        },
        mounted() {
            this.$bus.$on('showNewNotification', (item) => {
                this.alertStack.push(item);
            });
        },
        methods: {
            clearInterval() {
                if (this.interval !== null) {
                    clearInterval(this.interval);
                }
                this.interval = null;
                return this;
            },
            clearCounter() {
                if (this.counterInterval !== null) {
                    clearInterval(this.counterInterval);
                }
                this.counterInterval = null;
                return this;
            },
            setCounter() {
                const self = this;
                self.counter = 100;
                self.counterInterval = setInterval(() => {
                    self.counter--;
                }, 50);
                return this;
            },
            setInterval() {
                const self = this;
                self.interval = setInterval(() => {
                    self.alertStack.shift();
                }, 5000);
                return this;
            }
        }
    };
</script>

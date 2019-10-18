<template>
    <v-layout align-center justify-center>
        <v-flex xs12 lg4 xl3>
            <v-card class="border-20" elevation="7">
                <v-card-text class="pb-0 mb-0 pt-5">
                    <v-layout row wrap>
                        <!-- Loading Progress Bar Start -->
                        <v-flex xs12 class="pb-3" v-show="loading">
                            <v-progress-linear indeterminate />
                        </v-flex>
                        <!-- Loading Progress Bar End -->

                        <!-- Error Alert Box Start -->
                        <v-flex xs12 class="pb-3">
                            <v-alert
                                :value="error.has"
                                type="error"
                                transition="slide-y-transition"
                            >
                                {{ error.message }}
                            </v-alert>
                        </v-flex>
                        <!-- Error Alert Box End -->

                        <!-- Email Input Field Start -->
                        <v-flex xs12 class="pb-0 mb-0">
                            <v-text-field
                                v-model="email"
                                :label="$t('common.fields.email.label')"
                                :placeholder="$t('common.fields.email.placeholder')"
                                outlined
                            />
                        </v-flex>
                        <!-- Email Input Field End -->

                        <!-- Password Input Field Start -->
                        <v-flex xs12 class="pt-0 mt-0 pb-0 mb-0">
                            <v-sensitive-input
                                v-model="password"
                                :label="$t('common.fields.password.label')"
                                :placeholder="$t('common.fields.password.placeholder')"
                                :sensitive="true"
                                outlined
                            />
                        </v-flex>
                        <!-- Password Input Field End -->

                        <!-- Remember Me Field Start -->
                        <v-flex xs12 class="pt-0 mt-0">
                            <v-checkbox
                                v-model="rememberMe"
                                color="primary"
                                :label="$t('common.remember_me')"
                            />
                        </v-flex>
                        <!-- Remember Me Field End -->
                    </v-layout>
                </v-card-text>
                <v-card-actions class="mt-0 pt-0">
                    <v-layout row wrap>
                        <v-flex xs12 lg6>
                            <v-btn
                                color="amber"
                                block
                                outlined
                                @click.stop="resetPage"
                            >
                                {{ $t('common.clear_form') }}
                            </v-btn>
                        </v-flex>
                        <v-flex xs12 lg6>
                            <v-btn
                                color="primary"
                                block
                                @click.stop="authenticate"
                            >
                                {{ $t('common.log_in') }}
                            </v-btn>
                        </v-flex>
                        <v-flex xs12 text-center class="pt-2">
                            <v-btn
                                small
                                text
                                class="normal-case"
                                to="/"
                                nuxt
                            >
                                {{ $t('user.account.create.return') }}
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-card-actions>
            </v-card>
        </v-flex>
    </v-layout>
</template>
<script>
    export default {
        layout: 'login',
        data: () => ({
            loading: false,
            email: '',
            password: '',
            rememberMe: false,
            error: {
                has: false,
                message: ''
            }
        }),
        watch: {
            error: {
                handler (current, previous) {
                    if (current.has) {
                        setTimeout(() => {
                            this.error = {
                                has: false,
                                message: ''
                            };
                        }, 3000);
                    }
                },
                deep: true
            }
        },
        beforeDestroy() {
            this.resetPage();
        },
        mounted() {
            this.resetPage();
        },
        methods: {
            resetPage() {
                this.loading = false;
                this.email = '';
                this.password = '';
                this.rememberMe = false;
            },
            authenticate() {
                this.loading = true;
                this.$axios.post('account/authenticate', {
                    email: this.email,
                    password: this.password,
                    remember_me: this.rememberMe
                }).then(({ data }) => {
                    this.$store.dispatch('account/setToken', {
                        token: data.data.access_token,
                        type: data.data.token_type,
                        expiration: data.data.expires_at,
                        createCookie: true
                    });
                    setTimeout(() => {
                        this.loading = false;
                        this.$router.push({
                            path: '/'
                        });
                    }, 1500);
                }).catch(({ response }) => {
                    this.loading = false;
                    this.error = {
                        has: true,
                        message: this.$t(response.data.message)
                    };
                });
            }
        }
    };
</script>

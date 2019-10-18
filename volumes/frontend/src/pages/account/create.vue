<template>
    <v-layout align-center justify-center>
        <v-flex xs12 lg4 xl3>
            <v-card v-if="!accountCreated">
                <v-card-text class="pb-0 pt-">
                    <v-layout row wrap>
                        <!-- Username Input Field Start -->
                        <v-flex xs12>
                            <v-text-field
                                v-model.lazy="username"
                                :label="$t('user.account.create.username.label')"
                                :placeholder="$t('user.account.create.username.placeholder')"
                                :error-messages="usernameErrors"
                                :loading="checking.username"
                                outlined
                                @input="$v.username.$touch()"
                                @blur="checkForAvailabilityErrors('username')"
                                :disabled="disableAllFields"
                            />
                        </v-flex>
                        <!-- Username Input Field End -->

                        <!-- E-Mail Input Field Start -->
                        <v-flex xs12>
                            <v-text-field
                                v-model="email"
                                :label="$t('user.account.create.email.label')"
                                :placeholder="$t('user.account.create.email.placeholder')"
                                :error-messages="emailErrors"
                                :loading="checking.email"
                                outlined
                                @input="$v.email.$touch()"
                                @blur="checkForAvailabilityErrors('email')"
                                :disabled="disableAllFields"
                            />
                        </v-flex>
                        <!-- E-Mail Input Field End -->

                        <!-- Password Input Field Start -->
                        <v-flex xs12>
                            <v-text-field
                                v-model="password"
                                :label="$t('user.account.create.password.label')"
                                :placeholder="$t('user.account.create.password.placeholder')"
                                :error-messages="passwordErrors"
                                :append-icon="showPassword ? 'visibility' : 'visibility_off'"
                                :type="showPassword ? 'text' : 'password'"
                                outlined
                                @click:append="showPassword = !showPassword"
                                @input="$v.password.$touch()"
                                @blur="$v.password.$touch()"
                                :disabled="disableAllFields"
                            />
                        </v-flex>
                        <!-- Password Input Field End -->

                        <!-- Password Confirmation Input Field Start -->
                        <v-flex xs12>
                            <v-text-field
                                v-model="password_confirmation"
                                :label="$t('user.account.create.password_confirmation.label')"
                                :placeholder="$t('user.account.create.password_confirmation.placeholder')"
                                :error-messages="passwordConfirmationErrors"
                                :append-icon="showPasswordConfirmation ? 'visibility' : 'visibility_off'"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                outlined
                                @click:append="showPasswordConfirmation = !showPasswordConfirmation"
                                @input="$v.password_confirmation.$touch()"
                                @blur="$v.password_confirmation.$touch()"
                                :disabled="disableAllFields"
                            />
                        </v-flex>
                        <!-- Password Confirmation Input Field End -->
                    </v-layout>
                </v-card-text>
                <v-card-actions class="pt-0 pb-4">
                    <v-layout row wrap>
                        <v-flex xs12 lg6>
                            <v-btn
                                color="amber darken-2"
                                block
                                @click.stop="clearForm"
                            >
                                {{ $t('common.clear_form') }}
                            </v-btn>
                        </v-flex>
                        <v-flex xs12 lg6>
                            <v-btn
                                color="primary"
                                block
                                @click.stop="createAccount"
                                :disabled="!createAccountButtonEnabled"
                            >
                                {{ $t('common.create_account') }}
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
            <v-card v-if="accountCreated">
                <v-card-text class="pt-5 pb-0">
                    <v-layout row wrap text-center>
                        <v-flex xs12>
                            <v-icon color="green" size="75">
                                check
                            </v-icon>
                        </v-flex>
                        <v-flex xs12>
                            {{ $t('user.account.create.created') }}
                        </v-flex>
                        <v-flex xs12>
                            {{ $t('user.account.create.created_second') }}
                        </v-flex>
                    </v-layout>
                </v-card-text>
                <v-card-actions class="pt-2 pb-4">
                    <v-layout row wrap>
                        <v-flex xs12 text-center>
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
    import { validationMixin } from 'vuelidate';
    import {
        required,
        minLength,
        maxLength,
        email,
        sameAs
    } from 'vuelidate/lib/validators';

    export default {
        mixins: [
            validationMixin
        ],
        layout: 'login',
        data: () => ({
            showPassword: false,
            showPasswordConfirmation: false,
            // This is needed so Google can stop populating values automatically
            disableAllFields: true,
            checking: {
                username: false,
                email: false
            },
            accountCreated: false,

            username: '',
            usernameTaken: false,
            email: '',
            emailTaken: false,
            password: '',
            password_confirmation: '',
        }),
        validations: {
            username: {
                required,
                minLength: minLength(6),
                maxLength: maxLength(16)
            },
            email: {
                required,
                email
            },
            password: {
                required,
                minLength: minLength(8),
                maxLength: maxLength(16)
            },
            password_confirmation: {
                required,
                minLength: minLength(8),
                maxLength: maxLength(16),
                sameAs: sameAs('password')
            }
        },
        computed: {
            usernameErrors() {
                const errors = [];
                if (!this.$v.username.$dirty) { return errors; }
                !this.$v.username.minLength && errors.push(this.$t('user.account.create.username.minLength', { number: 6 }));
                !this.$v.username.maxLength && errors.push(this.$t('user.account.create.username.maxLength', { number: 16 }));
                !this.$v.username.required && errors.push(this.$t('user.account.create.username.required'));
                if (this.usernameTaken) {
                    errors.push(this.$t('user.account.create.username.isUnique'));
                }
                return errors;
            },
            emailErrors() {
                const errors = [];
                if (!this.$v.email.$dirty) { return errors; }
                !this.$v.email.email && errors.push(this.$t('user.account.create.email.email'));
                !this.$v.email.required && errors.push(this.$t('user.account.create.email.required'));
                if (this.emailTaken) {
                    errors.push(this.$t('user.account.create.email.isUnique'));
                }
                return errors;
            },
            passwordErrors() {
                const errors = [];
                if (!this.$v.password.$dirty) { return errors; }
                !this.$v.password.minLength && errors.push(this.$t('user.account.create.password.minLength', { number: 8 }));
                !this.$v.password.maxLength && errors.push(this.$t('user.account.create.password.maxLength', { number: 16 }));
                !this.$v.password.required && errors.push(this.$t('user.account.create.password.required'));
                return errors;
            },
            passwordConfirmationErrors() {
                const errors = [];
                if (!this.$v.password_confirmation.$dirty) { return errors; }
                !this.$v.password_confirmation.minLength && errors.push(this.$t('user.account.create.password_confirmation.minLength', { number: 8 }));
                !this.$v.password_confirmation.maxLength && errors.push(this.$t('user.account.create.password_confirmation.maxLength', { number: 16 }));
                !this.$v.password_confirmation.sameAs && errors.push(this.$t('user.account.create.password_confirmation.sameAs'));
                !this.$v.password_confirmation.required && errors.push(this.$t('user.account.create.password_confirmation.required'));
                return errors;
            },
            createAccountButtonEnabled() {
                let enabled = true;
                if (
                    (this.username.length === 0 || this.usernameErrors.length > 0)
                    || (this.email.length === 0 || this.emailErrors.length > 0)
                    || (this.password.length === 0 || this.passwordErrors.length > 0)
                    || (this.password_confirmation.length === 0 || this.passwordConfirmationErrors.length > 0)
                ) {
                    enabled = false;
                }
                return enabled;
            }
        },
        mounted() {
            setTimeout(() => {
                this.disableAllFields = false;
            }, 2000);
        },
        methods: {
            checkForAvailabilityErrors(fieldName) {
                const endpoints = {
                    username: {
                        url: 'account/username-availability-check',
                        value: this.username
                    },
                    email: {
                        url: 'account/email-availability-check',
                        value: this.email
                    }
                };
                this.checking[fieldName] = true;
                const target = endpoints[fieldName];
                let payload = {};
                payload[fieldName] = target.value;

                this.$axios.post(target.url, payload).then(({ data }) => {
                    const { available } = data.data;
                    this[`${fieldName}Taken`] = !available;
                    this.checking[fieldName] = false;
                });
            },
            clearForm() {
                this.showPassword = false;
                this.showPasswordConfirmation = false;
                this.checking = {
                    username: false,
                    email: false
                };
                this.username = '';
                this.email = '';
                this.password = '';
                this.password_confirmation = '';
                this.$v.$reset();
            },
            createAccount() {
                this.$v.$touch();
                if (this.createAccountButtonEnabled) {
                    const newAccount = {
                        username: this.username,
                        email: this.email,
                        password: this.password,
                        password_confirmation: this.password_confirmation
                    };
                    this.$axios.post('account/create', newAccount).then(() => {
                        this.accountCreated = true;
                    }).catch(({ response }) => {
                        console.error(response.data);
                    });
                } else {
                    console.error('Form has errors, cannot submit it');
                }
            },
        }
    };
</script>

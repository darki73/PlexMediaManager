<template>
    <v-layout align-center justify-center>
        <v-flex xs12 lg4 xl3>
            <v-card>
                <v-card-title>
                    Title
                </v-card-title>
                <v-card-text>
                    <v-layout row wrap>
                        <!-- Username Input Field Start -->
                        <v-flex xs12>
                            <v-text-field
                                v-model="username"
                                :label="$t('user.account.create.username.label')"
                                :placeholder="$t('user.account.create.username.placeholder')"
                                outlined
                            />
                        </v-flex>
                        <!-- Username Input Field End -->

                        <!-- E-Mail Input Field Start -->
                        <v-flex xs12>
                            <v-text-field
                                v-model="email"
                                :label="$t('user.account.create.email.label')"
                                :placeholder="$t('user.account.create.email.placeholder')"
                                outlined
                            />
                        </v-flex>
                        <!-- E-Mail Input Field End -->

                        <!-- Password Input Field Start -->
                        <v-flex xs12>
                            <v-sensitive-input
                                v-model="password"
                                :label="$t('user.account.create.password.label')"
                                :placeholder="$t('user.account.create.password.placeholder')"
                                outlined
                            />
                        </v-flex>
                        <!-- Password Input Field End -->

                        <!-- Password Confirmation Input Field Start -->
                        <v-flex xs12>
                            <v-sensitive-input
                                v-model="password"
                                :label="$t('user.account.create.password_confirmation.label')"
                                :placeholder="$t('user.account.create.password_confirmation.placeholder')"
                                outlined
                            />
                        </v-flex>
                        <!-- Password Confirmation Input Field End -->
                    </v-layout>
                </v-card-text>
                <v-card-actions>
                    <v-layout row wrap>
                        <v-flex xs12>
                            Button
                        </v-flex>
                        <v-flex xs12>
                            Button
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
            username: '',
            email: '',
            password: '',
            password_confirmation: '',
        })
    };
</script>

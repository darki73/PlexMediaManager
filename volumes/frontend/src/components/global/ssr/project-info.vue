<template>
    <v-layout row wrap>
        <v-flex xs12>
            <h2>Project Information | Информация о проекте</h2>
            <small>(~/components/global/ssr/project-info.vue)</small>
        </v-flex>
        <v-flex xs12>
            <v-layout row wrap>
                <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs6>
                            <h3>English Description</h3>
                            This project is aims to provide an interface suitable for both administrators and users to manage their Plex library.
                        </v-flex>
                        <v-flex xs6>
                            <h3>Описание на Русском</h3>
                            Цель данного проекта дать возможность администраторам и пользователям управлять их медиатекой Plex.
                        </v-flex>
                    </v-layout>
                </v-flex>

                <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs6>
                            <strong>
                                What is there for administrator:
                            </strong>
                            <br />
                            <ul>
                                <li>
                                    Ability to manage users and groups
                                </li>
                                <li>
                                    Ability to view mounted disks, their statistics and perform necessary operations on them
                                </li>
                                <li>
                                    Ability to view application logs
                                </li>
                                <li>
                                    Ability to download new / update existing series and movies using provided "Indexers"
                                </li>
                                <li>
                                    More other features
                                </li>
                            </ul>
                        </v-flex>
                        <v-flex xs6>
                            <strong>
                                Возможности администратора:
                            </strong>
                            <br />
                            <ul>
                                <li>
                                    Возможность управлять пользователями и группами
                                </li>
                                <li>
                                    Возможность просматривать подключенные диски, их статистику и управлять ими
                                </li>
                                <li>
                                    Возможность просматривать логи приложения
                                </li>
                                <li>
                                    Возможность добавлять / обновлять сериалы и фильмы используя "Индексеры"
                                </li>
                                <li>
                                    Множество других функций
                                </li>
                            </ul>
                        </v-flex>
                    </v-layout>
                </v-flex>
                <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs6>
                            <strong>
                                What is there for user:
                            </strong>
                            <br />
                            <ul>
                                <li>
                                    Ability to request access to Plex Media Server
                                </li>
                                <li>
                                    Ability to request certain movies / series
                                </li>
                                <li>
                                    Ability to be redirected to Plex when user wants to watch selected item
                                </li>
                                <li>
                                    More other features!
                                </li>
                            </ul>
                        </v-flex>
                        <v-flex xs6>
                            <strong>
                                Возможности пользователя:
                            </strong>
                            <br />
                            <ul>
                                <li>
                                    Возможность запросить доступ к серверу Plex
                                </li>
                                <li>
                                    Возможность "заказать" фильм или сериал
                                </li>
                                <li>
                                    Возможность просмотра выбранного медиа элемента в Plex
                                </li>
                                <li>
                                    Множество других функций
                                </li>
                            </ul>
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-flex>
    </v-layout>
</template>
<script>
    export default {
        name: 'project-info',
        data: () => ({})
    };
</script>

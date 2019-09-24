import colors from 'vuetify/es5/util/colors'

const pkg = require('./package');
const fs = require('fs');
const path = require('path');
require('dotenv').config({
    path: path.resolve(__dirname, '/.env')
});

const site = require('./config/site')(process.env);
const siteConfig = path.resolve(__dirname, 'src', 'config', 'site.json');
if (fs.existsSync(siteConfig)) {
    fs.unlinkSync(siteConfig);
}
fs.writeFileSync(siteConfig, JSON.stringify(site, null, 2));

function shouldForceHttps() {
    const force = process.env.FORCE_HTTPS === 'true';
    if (force) {
        return true;
    }
    return process.env.APP_ENV !== 'local';
}

function axiosApiUrl() {
    const desiredProtocol = shouldForceHttps() ? 'https://' : 'http://';
    return desiredProtocol.concat(process.env.API_URL, '/');
}

export default {
    mode: 'universal',
    srcDir: 'src/',

    server: {
        port: 3000,
        host: '0.0.0.0'
    },

    env: {
        PREFIXED_API_URL: axiosApiUrl()
    },

    head: {
        titleTemplate: '%s - ' + process.env.npm_package_name,
        title: process.env.npm_package_name || '',
        meta: [
            { charset: 'utf-8' },
            { name: 'viewport', content: 'width=device-width, initial-scale=1' },
            {
                hid: 'description',
                name: 'description',
                content: process.env.npm_package_description || ''
            }
        ],
        link: [
            { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
        ]
    },


    loading: { color: '#169cae' },

    css: [
        '~/assets/scss/application.scss',
    ],

    plugins: [
        '~/plugins/imports.js',
        '~/plugins/third-party/axios.js',
        '~/plugins/third-party/moment.js',
        '~/plugins/prototypes/bus.js',
        '~/components/global/ssr/index.js'
    ],

    buildModules: [
        '@nuxtjs/eslint-module',
        '@nuxtjs/vuetify'
    ],

    modules: [
        '@nuxtjs/axios',
        '@nuxtjs/pwa',
        ['nuxt-i18n', {
            locales: [
                {
                    code: 'en',
                    name: 'English',
                    file: 'en.js',
                    iso: 'en-US'
                },
                {
                    code: 'ru',
                    name: 'Русский',
                    file: 'en.js',
                    iso: 'ru-RU'
                }
            ],
            lazy: true,
            langDir: 'locales/',
            defaultLocale: 'en',
            seo: false,
            vueI18n: {
                fallbackLocale: 'en'
            },
            detectBrowserLanguage: {
                useCookie: true,
                cookieKey: 'i18n_redirected'
            },
            strategy: 'prefix_except_default',
        }],
    ],

    axios: {
        baseURL: axiosApiUrl(),
        https: shouldForceHttps(),
        proxyHeaders: true
    },

    vuetify: {
        customVariables: ['~/assets/scss/variables.scss'],
        theme: {
            dark: true,
            themes: {
                dark: {
                    primary: colors.blue.darken2,
                    accent: colors.grey.darken3,
                    secondary: colors.amber.darken3,
                    info: colors.teal.lighten1,
                    warning: colors.amber.base,
                    error: colors.deepOrange.accent4,
                    success: colors.green.accent3
                }
            }
        }
    },

    build: {
        extend(config, ctx) {}
    },

    router: {
        mode: 'history',
        middleware: [

        ]
    }
}

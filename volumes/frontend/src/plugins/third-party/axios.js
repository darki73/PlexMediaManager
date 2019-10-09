export default function ({
    $axios,
    store
}) {
    $axios.onRequest((config) => {
        config.baseURL = process.env.PREFIXED_API_URL;
        config.headers.common['Content-Type'] = 'application/json';
        config.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        config.headers.common['X-Requested-Time'] = new Date().getTime();
        if (store.getters['account/token'] !== null && store.getters['account/token'] !== undefined) {
            config.headers.common.Authorization = `${store.getters['account/token_type']} ${store.getters['account/token']}`;
        }
        if (store.getters['account/plex_token'] !== null && store.getters['account/plex_token'] !== undefined) {
            config.headers.common['X-Plex-Token'] = store.getters['account/plex_token'];
        }
    });
}

export default function ({
    $axios,
    store
}) {
    $axios.onRequest((config) => {
        config.baseURL = process.env.PREFIXED_API_URL;
        config.headers.common['Content-Type'] = 'application/json';
        config.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        config.headers.common['X-Requested-Time'] = new Date().getTime();
        // if (config.url.indexOf(config.baseURL + '/console/') !== false) {
        //     if (store.getters['console/token'] !== null && store.getters['console/token'] !== undefined) {
        //         config.headers.common.Authorization = `Bearer ${store.getters['console/token']}`;
        //     }
        // }
        // if (config.headers.common.Authorization === undefined || config.headers.common.Authorization === null) {
        //     if (store.getters['account/token'] !== null && store.getters['account/token'] !== undefined) {
        //         config.headers.common.Authorization = `Bearer ${store.getters['account/token']}`;
        //     }
        // }
    });
}

export default async function({
    app,
    store
}) {
    if (
        store.getters['account/token'] === null
        || store.getters['account/token'] === undefined
    ) {
        if (process.server) {
            store.dispatch('account/setToken', {
                token: app.$cookies.get('token'),
                type: app.$cookies.get('token_type'),
                expiration: app.$cookies.get('token_expiration'),
                createCookie: false
            });
        }
    }

    if (
        store.getters['account/token'] !== null
        && store.getters['account/token'] !== undefined
        && store.getters['account/token_type'] !== null
        && store.getters['account/token_type'] !== undefined
        && store.getters['account/token_expiration'] !== null
        && store.getters['account/token_expiration'] !== undefined
    ) {
        if (
            store.getters['account/user'] === null
            || store.getters['account/user'] === undefined
        ) {
            await store.dispatch('account/fetchUser');
        }
    }

    return Promise.resolve();
}

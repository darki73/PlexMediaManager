const types = {
    SET_ACCOUNT_TOKEN: 'SET_ACCOUNT_TOKEN',
    SET_PLEX_TOKEN: 'SET_PLEX_TOKEN',
    FETCH_USER_SUCCESS: 'FETCH_USER_SUCCESS',
    FETCH_USER_FAILURE: 'FETCH_USER_FAILURE',
    LOGOUT: 'LOGOUT'
};

export const state = () => ({
    token: null,
    token_type: null,
    token_expiration: null,
    plex_token: null,
    user: null
});

export const getters = {
    token: state => state.token,
    token_type: state => state.token_type,
    token_expiration: state => state.token_expiration,
    plex_token: state => state.plex_token,
    plex_authenticated: state => state.plex_token !== null && state.plex_token !== undefined,
    user: state => state.user,
    authenticated: state => state.user !== null && state.user !== undefined && state.token !== null
};

export const mutations = {
    [types.SET_ACCOUNT_TOKEN] (state, {
        token,
        type,
        expiration,
        createCookie
    }) {
        state.token = token;
        state.token_type = type;
        state.token_expiration = expiration;

        if (createCookie) {
            const expirationDate = new Date(expiration);

            // Set the token cookie itself
            this.$cookies.set('token', token, {
                path: '/',
                sameSite: true,
                expires: expirationDate
            });

            // Set the token type cookie
            this.$cookies.set('token_type', type, {
                path: '/',
                sameSite: true,
                expires: expirationDate
            });

            // Set the token expiration date cookie
            this.$cookies.set('token_expiration', expiration, {
                path: '/',
                sameSite: true,
                expires: expirationDate
            });
        }
    },
    [types.SET_PLEX_TOKEN] (state, {
        token,
        createCookie
    }) {
        state.plex_token = token;
        if (createCookie) {
            this.$cookies.set('plex_token', token, {
                path: '/',
                sameSite: true
            });
        }
    },
    [types.FETCH_USER_SUCCESS] (state, user) {
        state.user = user;
    },
    [types.FETCH_USER_FAILURE] (state) {
        state.token = null;
        state.token_type = null;
        state.token_expiration = null;
        state.user = null;
        this.$cookies.remove('token');
        this.$cookies.remove('token_type');
        this.$cookies.remove('token_expiration');
    },
    [types.LOGOUT] (state) {
        state.token = null;
        state.token_type = null;
        state.token_expiration = null;
        state.user = null;
        this.$cookies.remove('token');
        this.$cookies.remove('token_type');
        this.$cookies.remove('token_expiration');
    }
};

export const actions = {

    /**
     * Set account token, type and expiration date received from API
     * @param commit
     * @param tokenData
     * @returns {Promise<void>}
     */
    async setToken({ commit }, tokenData) {
        commit(types.SET_ACCOUNT_TOKEN, tokenData);
    },

    /**
     * Set plex token
     * @param commit
     * @param tokenData
     * @returns {Promise<void>}
     */
    async setPlexToken({ commit }, tokenData) {
        commit(types.SET_PLEX_TOKEN, tokenData);
    },

    /**
     * Logout User
     * @param commit
     * @returns {Promise<void>}
     */
    async logout({ commit }) {
        commit(types.LOGOUT);
    },

    /**
     * Fetch User Information
     * @param commit
     * @returns {Promise<void>}
     */
    async fetchUser({ commit }) {
        try {
            const { data } = await this.$axios.get('account/user');
            commit(types.FETCH_USER_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_USER_FAILURE);
        }
    }

};

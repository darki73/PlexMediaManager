const types = {
    FETCH_SERVERS_SUCCESS: 'FETCH_SERVERS_SUCCESS',
    FETCH_SERVERS_FAILURE: 'FETCH_SERVERS_FAILURE',
    SET_SELECTED_SERVER: 'SET_SELECTED_SERVER',
    FETCH_LIBRARIES_SUCCESS: 'FETCH_LIBRARIES_SUCCESS',
    FETCH_LIBRARIES_FAILURE: 'FETCH_LIBRARIES_FAILURE',
    FETCH_PLEX_USERS_SUCCESS: 'FETCH_PLEX_USERS_SUCCESS',
    FETCH_PLEX_USERS_FAILURE: 'FETCH_PLEX_USERS_FAILURE',
};

export const state = () => ({
    servers: [],
    selected_server: null,
    libraries: [],
    users: []
});

export const getters = {
    servers: state => state.servers,
    selected_server: state => state.selected_server,
    libraries: state => state.libraries,
    users: state => state.users,
};

export const mutations = {
    [types.FETCH_SERVERS_SUCCESS] (state, servers) {
        state.servers = servers;
    },
    [types.FETCH_SERVERS_FAILURE] (state) {
        state.servers = [];
    },
    [types.SET_SELECTED_SERVER] (state, server) {
        state.selected_server = server;
    },
    [types.FETCH_LIBRARIES_SUCCESS] (state, libraries) {
        state.libraries = libraries;
    },
    [types.FETCH_LIBRARIES_FAILURE] (state) {
        state.libraries = [];
    },
    [types.FETCH_PLEX_USERS_SUCCESS] (state, users) {
        state.users = users;
    },
    [types.FETCH_PLEX_USERS_FAILURE] (state) {
        state.users = [];
    },
};

export const actions = {

    /**
     * Get list of Plex servers available for access for account
     * @param commit
     * @param refresh
     * @returns {Promise<void>}
     */
    async fetchServersList({ commit }, refresh = false) {
        const requestUrl = (refresh) ? 'plex/servers/refresh' : 'plex/servers';
        try {
            const { data } = await this.$axios.get(requestUrl);
            commit(types.FETCH_SERVERS_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_SERVERS_FAILURE);
        }
    },

    /**
     * Update value for server which is selected as preferred by user
     * @param commit
     * @param server
     * @returns {Promise<void>}
     */
    async setSelectedServer({ commit }, server) {
        commit(types.SET_SELECTED_SERVER, server);
    },

    /**
     * Fetch list of all available libraries for the selected server
     * @param commit
     * @param selectedServer
     * @returns {Promise<void>}
     */
    async fetchLibrariesList({ commit }, selectedServer) {
        try {
            const { data } = await this.$axios.post('plex/libraries', {
                server: selectedServer
            });
            commit(types.FETCH_LIBRARIES_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_LIBRARIES_FAILURE);
        }
    },

    /**
     * Fetch list of plex users
     * @param commit
     * @returns {Promise<void>}
     */
    async fetchPlexUsers({ commit }) {
        try {
            const { data } = await this.$axios.get('dashboard/plex/users');
            commit(types.FETCH_PLEX_USERS_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_PLEX_USERS_FAILURE);
        }
    }

};

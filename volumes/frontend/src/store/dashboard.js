const types = {
    FETCH_ACCOUNTS_USERS_SUCCESS: 'FETCH_ACCOUNTS_USERS_SUCCESS',
    FETCH_ACCOUNTS_USERS_FAILURE: 'FETCH_ACCOUNTS_USERS_FAILURE',
    FETCH_ACCOUNTS_GROUPS_SUCCESS: 'FETCH_ACCOUNTS_GROUPS_SUCCESS',
    FETCH_ACCOUNTS_GROUPS_FAILURE: 'FETCH_ACCOUNTS_GROUPS_FAILURE',
    FETCH_INDEXERS_SUCCESS: 'FETCH_INDEXERS_SUCCESS',
    FETCH_INDEXERS_FAILURE: 'FETCH_INDEXERS_FAILURE',
    FETCH_SERVER_INFO_SUCCESS: 'FETCH_SERVER_INFO_SUCCESS',
    FETCH_SERVER_INFO_FAILURE: 'FETCH_SERVER_INFO_FAILURE',
    FETCH_STORAGE_DISKS_LIST_SUCCESS: 'FETCH_STORAGE_DISKS_LIST_SUCCESS',
    FETCH_STORAGE_DISKS_LIST_FAILURE: 'FETCH_STORAGE_DISKS_LIST_FAILURE',
    FETCH_LOGS_SUCCESS: 'FETCH_LOGS_SUCCESS',
    FETCH_LOGS_FAILURE: 'FETCH_LOGS_FAILURE',
    FETCH_REQUESTS_ALL_SUCCESS: 'FETCH_REQUESTS_ALL_SUCCESS',
    FETCH_REQUESTS_ALL_FAILURE: 'FETCH_REQUESTS_ALL_FAILURE',
    FETCH_TORRENTS_SUCCESS: 'FETCH_TORRENTS_SUCCESS',
    FETCH_TORRENTS_FAILURE: 'FETCH_TORRENTS_FAILURE',
};

export const state = () => ({
    server: null,
    accounts_users: [],
    accounts_groups: [],
    indexers: [],
    storage_disks: [],
    logs: [],
    requests: [],
    torrents: [],
});

export const getters = {
    accounts_users: state => state.accounts_users,
    accounts_groups: state => state.accounts_groups,
    indexers: state => state.indexers,
    server: state => state.server,
    storage_disks: state => state.storage_disks,
    logs: state => state.logs,
    requests: state => state.requests,
    torrents: state => state.torrents
};

export const mutations = {
    [types.FETCH_ACCOUNTS_USERS_SUCCESS] (state, users) {
        state.accounts_users = users;
    },
    [types.FETCH_ACCOUNTS_USERS_FAILURE] (state) {
        state.accounts_users = [];
    },
    [types.FETCH_ACCOUNTS_GROUPS_SUCCESS] (state, groups) {
        state.accounts_groups = groups;
    },
    [types.FETCH_ACCOUNTS_GROUPS_FAILURE] (state) {
        state.accounts_groups = [];
    },
    [types.FETCH_INDEXERS_SUCCESS] (state, indexers) {
        state.indexers = indexers;
    },
    [types.FETCH_INDEXERS_FAILURE] (state) {
        state.indexers = [];
    },
    [types.FETCH_SERVER_INFO_SUCCESS] (state, info) {
        state.server = info;
    },
    [types.FETCH_SERVER_INFO_FAILURE] (state) {
        state.server = null;
    },
    [types.FETCH_STORAGE_DISKS_LIST_SUCCESS] (state, list) {
        state.storage_disks = list;
    },
    [types.FETCH_STORAGE_DISKS_LIST_FAILURE] (state) {
        state.storage_disks = [];
    },
    [types.FETCH_LOGS_SUCCESS] (state, logs) {
        state.logs = logs;
    },
    [types.FETCH_LOGS_FAILURE] (state) {
        state.logs = [];
    },
    [types.FETCH_REQUESTS_ALL_SUCCESS] (state, requests) {
        state.requests = requests;
    },
    [types.FETCH_REQUESTS_ALL_FAILURE] (state) {
        state.requests = [];
    },
    [types.FETCH_TORRENTS_SUCCESS] (state, torrents) {
        state.torrents = torrents;
    },
    [types.FETCH_TORRENTS_FAILURE] (state) {
        state.torrents = [];
    }
};

export const actions = {

    /**
     * Fetch server information
     * @param commit
     * @returns {Promise<void>}
     */
    async fetchServerInformation({ commit }) {
        try {
            const { data } = await this.$axios.get('dashboard/server-information');
            commit(types.FETCH_SERVER_INFO_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_SERVER_INFO_FAILURE);
        }
    },

    /**
     * Fetch list of users
     * @param commit
     * @returns {Promise<void>}
     */
    async fetchAccountsUsers({ commit }) {
        try {
            const { data } = await this.$axios.get('dashboard/accounts/users/list');
            commit(types.FETCH_ACCOUNTS_USERS_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_ACCOUNTS_USERS_FAILURE);
        }
    },

    /**
     * Fetch list of groups and their permissions
     * @param commit
     * @returns {Promise<void>}
     */
    async fetchAccountsGroups({ commit }) {
        try {
            const { data } = await this.$axios.get('dashboard/accounts/groups/list');
            commit(types.FETCH_ACCOUNTS_GROUPS_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_ACCOUNTS_GROUPS_FAILURE);
        }
    },

    /**
     * Fetch list of indexers
     * @param commit
     * @returns {Promise<void>}
     */
    async fetchIndexers({ commit }) {
        try {
            const { data } = await this.$axios.get('dashboard/indexers/list');
            commit(types.FETCH_INDEXERS_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_INDEXERS_FAILURE);
        }
    },

    /**
     * Fetch list of disks
     * @param commit
     * @returns {Promise<void>}
     */
    async fetchStorageDisksList({ commit }) {
        try {
            const { data } = await this.$axios.get('dashboard/storage/disks/list');
            commit(types.FETCH_STORAGE_DISKS_LIST_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_STORAGE_DISKS_LIST_FAILURE);
        }
    },

    /**
     * Fetch all logs
     * @param commit
     * @returns {Promise<void>}
     */
    async fetchLogs({ commit }) {
        try {
            const { data } = await this.$axios.get('dashboard/logs');
            commit(types.FETCH_LOGS_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_LOGS_FAILURE);
        }
    },

    /**
     * Fetch all requests
     * @param commit
     * @returns {Promise<void>}
     */
    async fetchRequests({ commit }) {
        try {
            const { data } = await this.$axios.get('dashboard/requests');
            commit(types.FETCH_REQUESTS_ALL_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_REQUESTS_ALL_FAILURE);
        }
    },

    /**
     * Fetch list of all active torrents
     * @param commit
     * @returns {Promise<void>}
     */
    async fetchTorrentsList({ commit }) {
        try {
            const { data } = await this.$axios.get('dashboard/torrents/list');
            commit(types.FETCH_TORRENTS_SUCCESS, data.data);
        } catch (error) {
            commit(types.FETCH_TORRENTS_FAILURE);
        }
    }

};

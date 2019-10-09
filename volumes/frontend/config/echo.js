module.exports = function (environment) {
    const authenticationEndpoint = `//${environment.API_URL}/broadcasting/auth`;
    const selectedDriver = environment.BROADCAST_DRIVER;

    const proxy = environment.WS_PROXY === 'true';
    const secure = environment.WS_APP_SECURE === 'true';

    const availableDrivers = {
        redis: {
            host: null,
            port: 3389
        },
        pusher: {
            key: environment.PUSHER_APP_KEY,
            cluster: environment.PUSHER_APP_CLUSTER,
            encrypted: true
        },
        'websockets': {
            key: environment.WS_APP_KEY,
            encrypted: secure,
            proxy: proxy,
            wsHost: environment.WS_APP_HOST || environment.APP_URL,
            wsPort: secure ? 443 : 80,
            disableStats: true,
            cluster: 'local',
            withCluster: false,
            withEndpoint: true
        }
    };

    let configuration = {};
    let instance = null;
    switch (selectedDriver) {
        case 'redis':
            instance = availableDrivers[selectedDriver];
            const port = instance.port || 3389;
            const host = instance.host;
            configuration = {
                broadcaster: 'socket.io',
                authEndpoint: authenticationEndpoint,
                host: `${host}:${port}`
            };
            break;
        case 'pusher':
            instance = availableDrivers[selectedDriver];
            configuration = {
                broadcaster: 'pusher',
                authEndpoint: authenticationEndpoint,
                key: instance.key,
                cluster: instance.cluster,
                encrypted: instance.encrypted
            };
            break;
        case 'websockets':
            instance = availableDrivers[selectedDriver];
            configuration = {
                broadcaster: 'pusher',
                key: instance.key,
                wsHost: instance.wsHost,
                wssHost: instance.wsHost,
                disableStats: instance.disableStats,
                encrypted: instance.encrypted,
                enabledTransports: ['ws', 'flash']
            };

            if (instance.hasOwnProperty('proxy') && instance.proxy === false) {
                configuration.wsPort = instance.wsPort;
                configuration.wssPort = instance.wsPort;
            }

            if (instance.hasOwnProperty('withCluster') && instance.withCluster !== false) {
                configuration.cluster = instance.cluster;
            }

            if (instance.hasOwnProperty('withEndpoint') && instance.withEndpoint !== false) {
                configuration.authEndpoint = authenticationEndpoint;
            }

            break;
    }

    return configuration;
};

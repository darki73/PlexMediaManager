const pkg = require('../package');

module.exports = function (environment) {
    return {
        name: environment.APP_NAME,
        environment: environment.APP_ENV,
        debug: environment.APP_DEBUG === 'true',
        force_https: environment.FORCE_HTTPS === 'true',
        backend: {
            url: environment.API_URL,
            version: environment.APP_VERSION || '1.0.0'
        },
        frontend: {
            url: environment.APP_URL,
            version: pkg.version
        }
    };
};

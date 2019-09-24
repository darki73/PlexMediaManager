const path = require('path');

function resolve(dir) {
    return path.resolve(__dirname, dir);
}

module.exports = {
    resolve: {
        alias: {
            '~': resolve('src')
        }
    }
};

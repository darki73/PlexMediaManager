const path = require('path');

function resolve(dir) {
    return path.resolve(__dirname, dir);
}

module.exports = {
    root: true,
    env: {
        node: true,
    },
    extends: [
        'plugin:vue/essential',
        '@vue/airbnb',
    ],
    rules: {
        'import/extensions': ['off', 'always', {
            'js': 'never',
            'vue': 'never'
        }],
        "import/no-unresolved": "off",
        'no-console': 0,
        'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off',
        "indent": "off",
        "comma-dangle": 0,
        "eol-last": 0,
        "no-new": 0,
        "no-trailing-spaces": 0,
        "no-shadow": 0,
        "no-param-reassign": 0,
        "space-before-function-paren": 0,
        "no-use-before-define": 0,
        "new-parens": 0,
        "prefer-template": 0,
        "no-plusplus": 0,
        "no-confusing-arrow": 0,
        "quote-props": 0,
        "array-callback-return": 0,
        "consistent-return": 0,
        "no-prototype-builtins": 0,
        "max-len": 0,
        "func-names": 0,
        "comma-spacing": 0,
        "no-lonely-if": 0,
        "linebreak-style": 0,
        "no-case-declarations": 0,
        "no-constant-condition": 0,
        "object-shorthand": 0,
        "prefer-const": 0,
        "import/no-mutable-exports": 0,
        "class-methods-use-this": 0,
        "no-restricted-globals": 0,
        "no-useless-escape": 0,
        "no-extend-native": 0,
        "no-webpack-loader-syntax": 0,
        "no-unused-expressions": 0,
        "prefer-destructuring": 0,
        "import/no-webpack-loader-syntax": 0,
        "vue/no-side-effects-in-computed-properties": 0,
        "prefer-promise-reject-errors": 0,
        "no-unused-vars": ["error", { "args": "none" }]
    },
    parserOptions: {
        parser: 'babel-eslint',
    },
    settings: {
        'import/resolver': {
            webpack: { config: resolve('webpack.config.js'), },
        }
    }
};

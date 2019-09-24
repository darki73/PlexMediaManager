#!/usr/bin/env bash

if [[ "${APP_ENV}" = "production" ]]; then
    yarn install
    yarn build
    yarn start
else
    yarn install
    yarn nuxt
fi
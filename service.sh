#!/usr/bin/env bash

#application=$(basename "$PWD")
application="plexmediamanager"

services=("app" "roadrunner" "scheduler" "frontend" "nginx" "redis" "database" "queue" "torrent" "jackett" "traefik")
actions=("start" "stop" "restart" "shell" "rebuild")


# shellcheck disable=SC2199
if [[ ! "${services[@]}" =~ $1 ]]; then
    echo "Unknown service: ${1}"
    printf -v servicesString "%s, " "${services[@]}"
    echo "Available services: ${servicesString::-2}"
    exit 0
fi

# shellcheck disable=SC2199
if [[ ! "${actions[@]}" =~ $2 ]]; then
    echo "Unknown action: ${2}"
    printf -v actionsString "%s|" "${actions[@]}"
    echo "Available actions: ${actionsString::-1}"
    exit 0
fi

case "$2" in
    "start")
    docker-compose start "$1"
    ;;
    "stop")
    docker-compose stop "$1"
    ;;
    "restart")
    docker-compose restart "$1"
    ;;
    "shell")
    docker exec -it "${application}_${1}" /bin/sh
    ;;
    "rebuild")
    docker-compose stop "$1"
    docker-compose build "$1"
    docker-compose start "$1"
    ;;
esac
#!/usr/bin/env bash

application=$(basename "$PWD")

services=("app", "roadrunner", "scheduler", "frontend", "nginx", "redis", "database", "queue")
actions=("start", "stop", "restart", "shell", "rebuild")

if [[ ! "${services[@]}" =~ "${1}" ]]; then
    echo "Unknown service: ${1}"
    printf -v servicesString "%s" "${services[@]}"
    echo "Available services: ${servicesString}"
    exit 0
fi

if [[ ! "${actions[@]}" =~ "${2}" ]]; then
    echo "Unknown action: ${2}"
    echo "Available actions: start|stop|restart"
    exit 0
fi

case "$2" in
    "start")
    docker-compose start $1
    ;;
    "stop")
    docker-compose stop $1
    ;;
    "restart")
    docker-compose restart $1
    ;;
    "shell")
    docker exec -it "${application}_${1}_1" /bin/sh
    ;;
    "rebuild")
    docker-compose stop $1
    docker-compose build $1
    docker-compose start $1
    ;;
esac
#!/usr/bin/env bash

SLEEP_DELAY="${SLEEP_DELAY:-1}";

trap "echo SIGHUP" HUP
trap "echo Shutting down; exit" TERM

while :; do
  sleep "$SLEEP_DELAY";
done;

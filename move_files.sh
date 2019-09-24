#!/usr/bin/env bash

response=$(curl -sb -H "https://media-api.freedomcore.ru/torrent/completed");
readarray -t commands <<<"$response"

for i in "${commands[@]}"
do
    eval $i
done

curl -sb -H "https://media-api.freedomcore.ru/torrent/sync"

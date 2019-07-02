#!/bin/bash

action="${1}"

if [ "$action" == "start" ]; then
    docker build --rm -f "Dockerfile" -t wordpress:latest .
    docker-compose up
elif [ "$action" == "stop" ]; then
    docker-compose down
else 
    echo "action not passed."
fi
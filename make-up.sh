#!/bin/sh

export HOSTIP=$(docker-machine ip)
docker-compose rm -f
docker-compose pull
docker-compose up --build -d
docker-compose run php-pubsub-kafka /bin/bash

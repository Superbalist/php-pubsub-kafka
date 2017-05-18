#!/bin/sh

export HOSTIP=$(docker-machine ip)
docker-compose run php-pubsub-kafka /bin/bash

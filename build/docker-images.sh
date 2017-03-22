#!/bin/bash

set -ev

if [ "$TRAVIS_PHP_VERSION" != "7.0" ]; then
	echo "Skipping docker build on php7.0"
	exit 0;
fi

TAG=`if [ "$TRAVIS_BRANCH" == "master" ]; then echo "latest"; else echo $TRAVIS_BRANCH ; fi`

docker build -t weinstein/app -f deploy/app.docker .
docker build -t weinstein/web -f deploy/web.docker .

docker tag weinstein/app weinstein/app:$TAG
docker tag weinstein/web weinstein/web:$TAG

docker login -u $DOCKER_USER -p $DOCKER_PASSWORD
docker push weinstein/app
docker push weinstein/web


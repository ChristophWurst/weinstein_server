#!/bin/bash

set -ev

if [ "$TRAVIS_PHP_VERSION" != "7.1" ]; then
	echo "Skipping docker build on php7.0"
	exit 0;
fi

BRANCH=${TRAVIS_PULL_REQUEST_BRANCH:-$TRAVIS_BRANCH}
TAG=`if [ "$BRANCH" == "master" ]; then echo "latest"; else echo $BRANCH | awk '{gsub("/","_",$0)}1' ; fi`

docker build -t weinstein/app:$TAG -f deploy/app.docker .
docker build -t weinstein/web:$TAG -f deploy/web.docker .

docker login -u $DOCKER_USER -p $DOCKER_PASSWORD
docker push weinstein/app
docker push weinstein/web
